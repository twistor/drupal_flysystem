<?php

/**
 * @file
 * Contains \Drupal\flysystem\DrupalFlysystemCache.
 */

namespace Drupal\flysystem;

use Drupal\Core\Cache\CacheBackendInterface;
use League\Flysystem\Cached\Storage\AbstractCache;

/**
 * An adapter that allows Flysystem to use Drupal's cache system.
 */
class DrupalFlysystemCache extends AbstractCache {

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The cache key.
   *
   * @var string
   */
  protected $key;

  /**
   * Constructs a DrupalFlysystemCache object.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param string $key
   *   The cache key.
   */
  public function __construct(CacheBackendInterface $cache, $key) {
    $this->cacheBackend = $cache;
    $this->key = $key;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    if ($cache = $this->cacheBackend->get($this->key)) {
      $this->cache = $cache->data[0];
      $this->complete = $cache->data[1];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    $cleaned = $this->cleanContents($this->cache);
    $this->cacheBackend->set($this->key, [$cleaned, $this->complete]);
  }

}
