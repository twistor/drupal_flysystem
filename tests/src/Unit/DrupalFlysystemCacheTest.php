<?php

/**
 * @file
 * Contains \NoDrupal\Tests\flysystem\Unit\DrupalFlysystemCacheTest.
 */

namespace NoDrupal\Tests\flysystem\Unit;

use Drupal\Core\Cache\MemoryBackend;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\DrupalFlysystemCache;

/**
 * @coversDefaultClass \Drupal\flysystem\DrupalFlysystemCache
 * @group flysystem
 */
class DrupalFlysystemCacheTest extends UnitTestCase {

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $backend;

  /**
   * @var \League\Flysystem\Cached\CacheInterface
   */
  protected $cache;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->backend = new MemoryBackend('test');
    $this->cache = new DrupalFlysystemCache($this->backend, 'flysystem');
  }

  /**
   * @covers ::load
   * @covers ::__construct
   */
  public function testLoadKeyExists() {
    $this->backend->set('flysystem', [['test.txt' => true], []]);
    $this->cache->load();
    $this->assertTrue($this->cache->has('test.txt'));
  }

  /**
   * @covers ::load
   */
  public function testLoadKeyDoesntExist() {
    $this->cache->load();
    $this->assertNull($this->cache->has('test.txt'));
  }

  /**
   * @covers ::save
   */
  public function testSavePersistsToCache() {
    $this->cache->updateObject('test.txt', ['size' => 10]);
    $this->cache->save();
    $this->assertTrue(isset($this->backend->get('flysystem')->data[0]['test.txt']));
  }

}
