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

  use FlysystemUrlTrait;

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
  protected $isPublic;

  /**
   * Constructs a Local object.
   *
   * @param array $configuration
   *   Plugin configuration array.
   */
  public function __construct(array $configuration) {
    $this->root = $configuration['root'];

    $root = realpath($configuration['root']);
    $public = realpath($this->basePath());

    $this->isPublic = strpos($public, $root) === 0;
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
    if (!$this->isPublic) {
      return parent::getExternalUrl($uri);
    }

    list(, $path) = explode('://', $uri, 2);
    $path = str_replace('\\', '/', $path);

    return $GLOBALS['base_url'] . '/' . $this->basePath() . '/' . UrlHelper::encodePath($path);
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

}
