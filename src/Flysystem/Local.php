<?php

/**
 * @file
 * Contains \Drupal\flysystem\Flysystem\Local.
 */

namespace Drupal\flysystem\Flysystem;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Site\Settings;
use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use League\Flysystem\Adapter\Local as LocalAdapter;

/**
 * Drupal plugin for the "Local" Flysystem adapter.
 *
 * @Adapter(id = "local")
 */
class Local implements FlysystemPluginInterface {

  use FlysystemUrlTrait { getExternalUrl as getDownloadlUrl; }

  /**
   * The root of the local adapter.
   *
   * @var string
   */
  protected $root;

  /**
   * Whether the root is in the public path.
   *
   * @var bool
   */
  protected $publicPath;

  /**
   * Constructs a Local object.
   *
   * @param array $configuration
   *   Plugin configuration array.
   */
  public function __construct(array $configuration) {
    $this->root = $configuration['root'];
    $this->publicPath = $this->pathIsPublic($this->root);
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

    return $GLOBALS['base_url'] . '/' . UrlHelper::encodePath($path);
  }

  /**
   * Returns the base path for public://.
   *
   * @return string
   *   The base path for public:// typically sites/default/files.
   */
  protected static function basePath() {
    return Settings::get('file_public_path', conf_path() . '/files');
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
    $base_path = $this->basePath();

    $public = realpath($base_path);
    $root = realpath($root);

    if ($public === FALSE || $root === FALSE) {
      return FALSE;
    }

    // The same directory.
    if ($public === $root) {
      return $base_path;
    }

    if (strpos($root, $public) !== 0) {
      return FALSE;
    }

    if (($subpath = substr($root, strlen($public))) && $subpath[0] === DIRECTORY_SEPARATOR) {
      return $base_path . '/' . ltrim($subpath, DIRECTORY_SEPARATOR);
    }

    return FALSE;
  }

}
