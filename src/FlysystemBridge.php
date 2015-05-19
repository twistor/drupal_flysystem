<?php

/**
 * @file
 * Contains \Drupal\flysystem\FlysystemBridge.
 */

namespace Drupal\flysystem;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Routing\UrlGeneratorTrait;
use Drupal\Core\Site\Settings;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\Replicate\ReplicateAdapter;
use Twistor\FlysystemStreamWrapper;

/**
 * An adapter for Flysystem to StreamWrapperInterface.
 */
class FlysystemBridge extends FlysystemStreamWrapper implements StreamWrapperInterface {

  use UrlGeneratorTrait;

  /**
   * A static class for plugins.
   *
   * @var array
   */
  protected static $plugins = [];

  /**
   * {@inheritdoc}
   */
  public static function getType() {
    return StreamWrapperInterface::WRITE_VISIBLE;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return t('Flysystem: @scheme', ['@scheme' => $this->getProtocol()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('Flysystem: @scheme', ['@scheme' => $this->getProtocol()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getUri() {
    return $this->uri;
  }

  /**
   * {@inheritdoc}
   */
  public function setUri($uri) {
    $this->uri = $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl() {
    return $this->getPluginFormScheme($this->getProtocol())->getExternalUrl($this->uri);
  }

  /**
   * {@inheritdoc}
   */
  public function realpath() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function dirname($uri = NULL) {
    if (!isset($uri)) {
      $uri = $this->uri;
    }

    list($scheme, $target) = explode('://', $uri, 2);
    // If there's no scheme, assume a regular directory path.
    if (!isset($target)) {
      $target = $scheme;
      $scheme = NULL;
    }

    $dirname = ltrim(dirname($target), '\/');

    if ($dirname === '.') {
      $dirname = '';
    }

    return isset($scheme) ? $scheme . '://' . $dirname : $dirname;
  }

  /**
   * Finds the settings for a given scheme.
   *
   * @param string $scheme
   *   The scheme.
   *
   * @return array
   *   The settings array from settings.php.
   */
  protected static function getSettingsForScheme($scheme) {
    $schemes = Settings::get('flysystem', []);

    $settings = isset($schemes[$scheme]) ? $schemes[$scheme] : [];

    return $settings += [
      'type' => '',
      'config' => [],
      'replicate' => FALSE,
      'cache' => FALSE,
      'prefix' => FALSE,
    ];
  }

  /**
   * Returns the plugin for a given scheme.
   *
   * @param string $scheme
   *   The scheme.
   *
   * @return \Drupal\flysystem\Plugin\FlysystemPluginInterface
   *   The plugin for the scheme.
   */
  protected static function getPluginFormScheme($scheme) {
    if (isset(static::$plugins[$scheme])) {
      return static::$plugins[$scheme];
    }

    $settings = static::getSettingsForScheme($scheme);

    $plugin = \Drupal::service('plugin.manager.flysystem')->createInstance($settings['type'], $settings['config']);
    static::$plugins[$scheme] = $plugin;

    return $plugin;
  }

  /**
   * Returns the adapter for the current scheme.
   *
   * @param string $scheme
   *   The scheme to find an adapter for.
   *
   * @return \League\Flysystem\AdapterInterface
   *   The correct adapter from settings.
   */
  protected static function getAdapterForScheme($scheme) {
    $settings = static::getSettingsForScheme($scheme);
    $adapter = static::getPluginFormScheme($scheme)->getAdapter();

    if ($settings['replicate']) {
      $replica = static::getAdapterForScheme($settings['replicate']);
      $adapter = new ReplicateAdapter($adapter, $replica);
    }

    if ($settings['cache']) {
      $adapter = new CachedAdapter($adapter, \Drupal::service('flysystem_cache'));
    }

    return $adapter;
  }

  /**
   * Returns the filesystem for a given scheme.
   *
   * @param string $scheme
   *   The scheme.
   *
   * @return \League\Flysystem\FilesystemInterface
   *   The filesystem for the scheme.
   */
  public static function getFilesystemForScheme($scheme) {
    if (!isset(static::$filesystems[$scheme])) {
      static::$filesystems[$scheme] = new Filesystem(static::getAdapterForScheme($scheme));
    }

    return static::$filesystems[$scheme];
  }

  /**
   * {@inheritdoc}
   */
  protected function getFilesystem() {
    if (!isset($this->filesystem)) {
      $this->filesystem = $this->getFilesystemForScheme($this->getProtocol());
    }

    return $this->filesystem;
  }

  /**
   * Sets the filesystem.
   *
   * @param \League\Flysystem\FilesystemInterface $filesystem
   *   The filesystem.
   *
   * @internal Only used during tests.
   */
  public function setFileSystem(FilesystemInterface $filesystem) {
    $this->filesystem = $filesystem;
  }

}
