<?php

namespace Drupal\flysystem\Flysystem\Adapter;

/**
 * A filesystem item stored in the Drupal cache.
 */
class CacheItem {

  /**
   * The array of metadata for the item.
   *
   * @var array
   */
  protected $metadata = [];

  /**
   * Returns the metadata for the item.
   *
   * @return array
   *   The array of metadata for the item.
   */
  public function getMetadata() {
    return $this->metadata;
  }

  /**
   * Updates the metadata for the item.
   *
   * @param array $metadata
   *   The array of metadata for the item.
   */
  public function updateMetadata(array $metadata) {
    static $keys = [
      'size' => TRUE,
      'mimetype' => TRUE,
      'visibility' => TRUE,
      'timestamp' => TRUE,
      'type' => TRUE,
    ];

    $this->metadata = array_intersect_key($metadata, $keys) + $this->metadata;
  }

}
