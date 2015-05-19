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
   * A map from adapter type to adapter factory.
   *
   * @var array
   *
   * @todo  Figure out a way for other modules to register adapters.
   */
  protected static $adapterMap = [
    'dropbox' => 'Drupal\flysystem\AdapterFactory\Dropbox',
    'ftp' => 'Drupal\flysystem\AdapterFactory\Ftp',
    'local' => 'Drupal\flysystem\AdapterFactory\Local',
    'rackspace' => 'Drupal\flysystem\AdapterFactory\Rackspace',
    's3' => 'Drupal\flysystem\AdapterFactory\S3',
    'sftp' => 'Drupal\flysystem\AdapterFactory\Sftp',
    'zip' => 'Drupal\flysystem\AdapterFactory\Zip',
  ];

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
    $scheme = $this->getProtocol();
    $path = str_replace('\\', '/', $this->getTarget());

    $settings = $this->getSettingsForScheme($scheme);
    if ($settings['prefix']) {
      return $settings['prefix'] . UrlHelper::encodePath($path);
    }

    return $this->url('flysystem.download', ['scheme' => $scheme, 'path' => $path], ['absolute' => TRUE]);
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
  protected function getSettingsForScheme($scheme) {
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
   * Returns the adapter for the current scheme.
   *
   * @param string $scheme
   *   The scheme to find an adapter for.
   *
   * @return \League\Flysystem\AdapterInterface
   *   The correct adapter from settings.
   */
  protected function getNewAdapter($scheme) {
    $settings = $this->getSettingsForScheme($scheme);

    if (isset(static::$adapterMap[$settings['type']])) {
      $factory = static::$adapterMap[$settings['type']];
      $adapter = $factory::create($settings['config']);
    }
    else {
      $adapter = new NullAdapter();
    }

    if ($settings['replicate']) {
      $replica = $this->getNewAdapter($settings['replicate']);
      $adapter = new ReplicateAdapter($adapter, $replica);
    }

    if ($settings['cache']) {
      $store = \Drupal::service('flysystem_cache');
      $adapter = new CachedAdapter($adapter, $store);
    }

    return $adapter;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFilesystem() {
    if (isset($this->filesystem)) {
      return $this->filesystem;
    }

    $scheme = $this->getProtocol();

    if (!isset(static::$filesystems[$scheme])) {
      static::$filesystems[$scheme] = new Filesystem($this->getNewAdapter($scheme));
    }

    $this->filesystem = static::$filesystems[$scheme];

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
