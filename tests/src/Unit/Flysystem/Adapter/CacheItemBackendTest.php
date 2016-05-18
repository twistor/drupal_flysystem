<?php

namespace Drupal\Tests\flysystem\Unit\Flysystem\Adapter;

use Drupal\Core\Cache\MemoryBackend;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\Flysystem\Adapter\CacheItem;
use Drupal\flysystem\Flysystem\Adapter\CacheItemBackend;

/**
 * @group flysystem
 *
 * @coversDefaultClass \Drupal\flysystem\Flysystem\Adapter\CacheItemBackend
 * @covers \Drupal\flysystem\Flysystem\Adapter\CacheItemBackend
 */
class CacheItemBackendTest extends UnitTestCase {

  /**
   * The cache backend used in the CacheItemBackend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The cache item backend to test.
   *
   * @var \Drupal\flysystem\Flysystem\Adapter\CacheItemBackend
   */
  protected $cacheItemBackend;

  /**
   * {@inheritdoc}
   */
  public function setup() {
    $this->cacheBackend = new MemoryBackend('foo');
    $this->cacheItemBackend = new CacheItemBackend('test-scheme', $this->cacheBackend);
  }

  /**
   * Tests whether a cache item exists.
   */
  public function testHas() {
    $this->assertFalse($this->cacheItemBackend->has('test.txt'));
  }

  /**
   * Tests loading a cache item from the cache.
   */
  public function testSetIsLoaded() {
    $cache_item = new CacheItem();
    $cache_item->updateMetadata(['mimetype' => 'test_mimetype']);
    $this->cacheItemBackend->set('test_path', $cache_item);

    $metadata = $this->cacheItemBackend->load('test_path')->getMetadata();
    $this->assertSame('test_mimetype', $metadata['mimetype']);
  }

  /**
   * Tests when loading a cache item creates a new item.
   */
  public function testLoadMiss() {
    $item = $this->cacheItemBackend->load('test_path');
    $this->assertInstanceOf(CacheItem::class, $item);
  }

  /**
   * Tests deleting by a path.
   */
  public function testDelete() {
    $cache_item = new CacheItem();
    $cache_item->updateMetadata(['mimetype' => 'test_mimetype']);

    $this->cacheItemBackend->set('test_path', $cache_item);
    $this->cacheItemBackend->delete('test_path');

    $metadata = $this->cacheItemBackend->load('test_path')->getMetadata();
    $this->assertTrue(empty($metadata['mimetype']));
  }

  /**
   * Tests deleting multiple items at once.
   */
  public function testDeleteMultiple() {
    $cache_item_one = new CacheItem();
    $cache_item_two = new CacheItem();

    $this->cacheItemBackend->set('one', $cache_item_one);
    $this->cacheItemBackend->set('two', $cache_item_two);

    $this->cacheItemBackend->deleteMultiple(['one', 'two']);

    $this->assertNotSame($cache_item_one, $this->cacheItemBackend->load('one'));
    $this->assertNotSame($cache_item_two, $this->cacheItemBackend->load('two'));
  }

}
