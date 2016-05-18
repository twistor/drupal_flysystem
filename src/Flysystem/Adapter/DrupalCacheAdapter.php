<?php

namespace Drupal\flysystem\Flysystem\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;

/**
 * A Flysystem adapter implementing caching with Drupal's Cache API.
 */
class DrupalCacheAdapter implements AdapterInterface {

  /**
   * The Flysystem adapter to cache data for.
   *
   * @var \League\Flysystem\AdapterInterface
   */
  protected $adapter;

  /**
   * The cache backend to store data in.
   *
   * @var \Drupal\flysystem\Flysystem\Adapter\CacheItemBackend
   */
  protected $cacheItemBackend;

  /**
   * The scheme of the stream wrapper used for this adapter.
   *
   * @var string
   */
  protected $scheme;

  /**
   * Constructs a new caching Flysystem adapter.
   *
   * @param string $scheme
   *   The scheme of the stream wrapper used for this adapter.
   * @param \League\Flysystem\AdapterInterface $adapter
   *   The flysystem adapter to cache data for.
   * @param \Drupal\flysystem\Flysystem\Adapter\CacheItemBackend $cacheItemBackend
   *   The cache backend to store data in.
   */
  public function __construct($scheme, AdapterInterface $adapter, CacheItemBackend $cacheItemBackend) {
    $this->scheme = $scheme;
    $this->adapter = $adapter;
    $this->cacheItemBackend = $cacheItemBackend;
  }

  /**
   * {@inheritdoc}
   */
  public function write($path, $contents, Config $config) {
    $metadata = $this->adapter->write($path, $contents, $config);

    return $this->updateMetadata($path, $metadata);
  }

  /**
   * {@inheritdoc}
   */
  public function writeStream($path, $resource, Config $config) {
    $metadata = $this->adapter->writeStream($path, $resource, $config);

    return $this->updateMetadata($path, $metadata);
  }

  /**
   * {@inheritdoc}
   */
  public function update($path, $contents, Config $config) {
    $metadata = $this->adapter->update($path, $contents, $config);

    return $this->updateMetadata($path, $metadata);
  }

  /**
   * {@inheritdoc}
   */
  public function updateStream($path, $resource, Config $config) {
    $metadata = $this->adapter->updateStream($path, $resource, $config);

    return $this->updateMetadata($path, $metadata);
  }

  /**
   * {@inheritdoc}
   */
  public function rename($path, $newpath) {
    $result = $this->adapter->rename($path, $newpath);

    if ($result) {
      $item = $this->cacheItemBackend->load($path);
      $newitem = clone $item;
      $this->cacheItemBackend->set($newpath, $newitem);
      $this->cacheItemBackend->delete($path);
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function copy($path, $newpath) {
    $result = $this->adapter->copy($path, $newpath);

    if ($result) {
      $item = $this->cacheItemBackend->load($path);
      $newitem = clone $item;
      $this->cacheItemBackend->set($newpath, $newitem);
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function delete($path) {
    $result = $this->adapter->delete($path);

    if ($result) {
      $this->cacheItemBackend->delete($path);
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteDir($dirname) {
    // Before the delete we need to know what files are in the directory.
    $contents = $this->adapter->listContents($dirname, TRUE);

    $result = $this->adapter->deleteDir($dirname);

    if ($result) {
      $paths = array_column($contents, 'path');
      $this->cacheItemBackend->deleteMultiple($paths);
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function createDir($dirname, Config $config) {
    $metadata = $this->adapter->createDir($dirname, $config);

    // Warm the metadata cache.
    if ($metadata) {
      $item = new CacheItem();
      $item->updateMetadata($metadata);
      $this->cacheItemBackend->set($dirname, $item);
    }

    return $metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function setVisibility($path, $visibility) {
    $metadata = $this->adapter->setVisibility($path, $visibility);

    return $this->updateMetadata($path, $metadata);
  }

  /**
   * {@inheritdoc}
   */
  public function has($path) {
    if ($this->cacheItemBackend->has($path)) {
      return TRUE;
    }

    // Always check the upstream adapter for new files.
    // TODO: This could be a good place for a microcache?
    return $this->adapter->has($path);
  }

  /**
   * {@inheritdoc}
   */
  public function read($path) {
    return $this->adapter->read($path);
  }

  /**
   * {@inheritdoc}
   */
  public function readStream($path) {
    return $this->adapter->readStream($path);
  }

  /**
   * {@inheritdoc}
   */
  public function listContents($directory = '', $recursive = FALSE) {
    // Don't cache directory listings to avoid having to keep track of
    // incomplete cache entries.
    // TODO: This could be a good place for a microcache?
    return $this->adapter->listContents($directory, $recursive);
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata($path) {
    $item = $this->cacheItemBackend->load($path);

    if ($metadata = $item->getMetadata()) {
      return $metadata;
    }

    $metadata = $this->adapter->getMetadata($path);
    $item->updateMetadata($metadata);
    $this->cacheItemBackend->set($path, $item);

    return $metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function getSize($path) {
    return $this->fetchMetadataKey($path, 'size');
  }

  /**
   * {@inheritdoc}
   */
  public function getMimetype($path) {
    return $this->fetchMetadataKey($path, 'mimetype');
  }

  /**
   * {@inheritdoc}
   */
  public function getTimestamp($path) {
    return $this->fetchMetadataKey($path, 'timestamp');
  }

  /**
   * {@inheritdoc}
   */
  public function getVisibility($path) {
    return $this->fetchMetadataKey($path, 'visibility');
  }

  /**
   * Fetches a specific key from metadata.
   *
   * @param string $path
   *   The path to load metadata for.
   * @param string $key
   *   The key in metadata, such as 'mimetype', to load metadata for.
   *
   * @return array
   *   The array of metadata.
   */
  protected function fetchMetadataKey($path, $key) {
    $item = $this->cacheItemBackend->load($path);

    if (($metadata = $item->getMetadata()) && isset($metadata[$key])) {
      return $metadata;
    }

    $method = 'get' . ucfirst($key);

    return $this->updateMetadata($path, $this->adapter->$method($path));
  }

  /**
   * Updates the metadata for a given path.
   *
   * @param string $path
   *   The path of file file or directory.
   * @param array|false $metadata
   *   The metadata to update.
   *
   * @return array|false
   *   Returns the value passed in as metadata.
   */
  protected function updateMetadata($path, $metadata) {
    if (!empty($metadata)) {
      $item = $this->cacheItemBackend->load($path);
      $item->updateMetadata($metadata);
      $this->cacheItemBackend->set($path, $item);
    }

    return $metadata;
  }

}
