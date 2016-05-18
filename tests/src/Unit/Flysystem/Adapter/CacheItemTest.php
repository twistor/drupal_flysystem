<?php

namespace Drupal\Tests\flysystem\Unit\Flysystem\Adapter;

use Drupal\flysystem\Flysystem\Adapter\CacheItem;
use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\flysystem\Flysystem\Adapter\CacheItem.
 *
 * @group flysystem
 *
 * @coversDefaultClass \Drupal\flysystem\Flysystem\Adapter\CacheItem
 * @covers \Drupal\flysystem\Flysystem\Adapter\CacheItem
 */
class CacheItemTest extends UnitTestCase {

  /**
   * Tests metadata updating and getting.
   */
  public function test() {
    $cache_item = new CacheItem();

    $metadata = [
      'size' => 1234,
      'mimetype' => 'test_mimetype',
      'visibility' => 'public',
      'timestamp' => 123456,
      'type' => 'file',
      'contents' => 'test contents',
      'path' => 'file_path',
    ];

    $cache_item->updateMetadata($metadata);

    unset($metadata['contents'], $metadata['path']);

    $this->assertSame($metadata, $cache_item->getMetadata());

  }

}
