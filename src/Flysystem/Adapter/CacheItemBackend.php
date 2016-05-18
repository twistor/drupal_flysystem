<?php

namespace Drupal\flysystem\Flysystem\Adapter;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Storage backend for cache items.
 *
 * This class is separated out from CacheItems so we can easily test loading,
 * saving, and deleting separately from the logic to reach back to a child
 * Flysystem adapter.
 */
class CacheItemBackend {

  /**
   * The Drupal cache backend to store data in.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The scheme this cache is managing.
   *
   * @var string
   */
  protected $scheme;

  /**
   * Constructs a new CacheItemBackend.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The Drupal cache backend to store items in.
   */
  public function __construct($scheme, CacheBackendInterface $cacheBackend) {
    $this->scheme = $scheme;
    $this->cacheBackend = $cacheBackend;
  }

  /**
   * Returns whether the cache item exists.
   *
   * @param string $path
   *   The path of the cache item.
   *
   * @return bool
   *   True if the item exists, false if not.
   */
  public function has($path) {
    return (bool) $this->cacheBackend->get($this->getCacheKey($path));
  }

  /**
   * Loads a cache item for a given path.
   *
   * @param string $path
   *   The path of the item to load.
   *
   * @return \Drupal\flysystem\Flysystem\Adapter\CacheItem
   *   The cache item, or a new cache item if one isn't in the cache.
   */
  public function load($path) {
    $key = $this->getCacheKey($path);

    if ($cached = $this->cacheBackend->get($key)) {
      /** @var \Drupal\flysystem\Flysystem\Adapter\CacheItem $item */
      $item = $cached->data;
    }
    else {
      $item = new CacheItem();
    }

    return $item;
  }

  /**
   * Sets a cache item in the backend.
   *
   * @param string $path
   *   The file path.
   * @param \Drupal\flysystem\Flysystem\Adapter\CacheItem $item
   *   The item to set.
   */
  public function set($path, CacheItem $item) {
    $this->cacheBackend->set($this->getCacheKey($path), $item);
  }

  /**
   * Deletes an item by the path.
   *
   * @param string $path
   *   The path of the item to delete.
   */
  public function delete($path) {
    $this->deleteMultiple([$path]);
  }

  /**
   * Deletes multiple paths.
   *
   * @param array $paths
   *   The array of paths to delete.
   */
  public function deleteMultiple(array $paths) {
    $keys = [];
    foreach ($paths as $path) {
      $keys[] = $this->getCacheKey($path);
    }
    $this->cacheBackend->deleteMultiple($keys);
  }

  /**
   * Gets the cache key for a cache item.
   *
   * @param string $path
   *   The path of the cache item.
   *
   * @return string
   *   A hashed key suitable for use in a cache.
   */
  protected function getCacheKey($path) {
    return Crypt::hashBase64($this->scheme . '://' . $path);
  }

}
