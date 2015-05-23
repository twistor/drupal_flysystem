<?php

/**
 * @file
 * Contains \Drupal\flysystem\Flysystem\Local.
 */

namespace Drupal\flysystem\Flysystem;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use League\Flysystem\Adapter\Local as LocalAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drupal plugin for the "Local" Flysystem adapter.
 *
 * @Adapter(id = "local")
 */
class Local implements FlysystemPluginInterface, ContainerFactoryPluginInterface {

  use FlysystemUrlTrait {
    getExternalUrl as getDownloadlUrl;
  }

  /**
   * The path to the public files.
   *
   * @var string
   */
  protected $basePath;

  /**
   * Whether the root is in the public path.
   *
   * @var string|false
   */
  protected $publicPath;

  /**
   * The root of the local adapter.
   *
   * @var string
   */
  protected $root;

  /**
   * Constructs a Local object.
   *
   * @param string $base_path
   *   The path to the public files directory.
   * @param string $root
   *   The of the adapter's filesystem.
   */
  public function __construct($base_path, $root) {
    $this->root = $root;
    $this->basePath = $base_path;
    $this->publicPath = $this->pathIsPublic($root);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $default = $container->get('kernel')->getSitePath() . '/files';

    $base_path = $container
      ->get('settings')
      ->get('file_public_path', $default);

    return new static($base_path, $configuration['root']);
  }

  /**
   * {@inheritdoc}
   */
  public function getAdapter() {
    return new LocalAdapter($this->root);
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl($uri) {
    if (!$this->publicPath) {
      return $this->getDownloadlUrl($uri);
    }

    $path = str_replace('\\', '/', $this->publicPath . '/' . $this->getTarget($uri));

    return $this->getUrlGenerator()->generateFromPath($path, ['absolute' => TRUE]);
  }

  /**
   * Determines if the path is inside the public path.
   *
   * @param string $root
   *   The root path.
   *
   * @return string|false
   *   The public path, or false.
   */
  protected function pathIsPublic($root) {
    $public = realpath($this->basePath);
    $root = realpath($root);

    if ($public === FALSE || $root === FALSE) {
      return FALSE;
    }

    // The same directory.
    if ($public === $root) {
      return $this->basePath;
    }

    if (strpos($root, $public) !== 0) {
      return FALSE;
    }

    if (($subpath = substr($root, strlen($public))) && $subpath[0] === DIRECTORY_SEPARATOR) {
      return $this->basePath . '/' . ltrim($subpath, DIRECTORY_SEPARATOR);
    }

    return FALSE;
  }

}
