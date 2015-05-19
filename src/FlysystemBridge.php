<?php

/**
 * @file
 * Contains \Drupal\flysystem\FlysystemBridge.
 */

namespace Drupal\flysystem;

use Drupal\Core\Routing\UrlGeneratorTrait;
use Drupal\Core\Site\Settings;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
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
    list($scheme, $path) = explode('://', $this->uri, 2);
    $path = str_replace('\\', '/', $path);
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
   * Returns the adapter for the current scheme.
   *
   * @return \League\Flysystem\AdapterInterface
   *   The correct adapter from settings.
   */
  protected function getNewAdapter() {
    $schemes = Settings::get('flysystem', []);
    $scheme = $this->getProtocol();

    $type = isset($schemes[$scheme]['type']) ? $schemes[$scheme]['type'] : '';
    $config = isset($schemes[$scheme]['config']) ? $schemes[$scheme]['config'] : [];

    if (isset(static::$adapterMap[$type])) {
      $factory = static::$adapterMap[$type];
      return $factory::create($config);
    }

    return new NullAdapter();
  }

  /**
   * {@inheritdoc}
   */
  protected function getFilesystem() {
    $scheme = $this->getProtocol();

    if (isset(static::$filesystems[$scheme])) {
      return static::$filesystems[$scheme];
    }

    $store = \Drupal::service('flysystem_cache');
    $adapter = new CachedAdapter($this->getNewAdapter(), $store);
    static::$filesystems[$scheme] = new Filesystem($adapter);

    return static::$filesystems[$scheme];
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
