<?php

/**
 * @file
 * Contains \NoDrupal\Tests\flysystem\Unit\DrupalFlysystemCacheTest.
 */

namespace NoDrupal\Tests\flysystem\Unit;

use Drupal\Core\Cache\MemoryBackend;
use Drupal\flysystem\DrupalFlysystemCache;

/**
 * @coversDefaultClass \Drupal\flysystem\DrupalFlysystemCache
 * @group flysystem
 */
class DrupalFlysystemCacheTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers \Drupal\flysystem\DrupalFlysystemCache
   */
  public function test() {
    $backend = new MemoryBackend('test');
    $cache = new DrupalFlysystemCache($backend, 'flysystem');
    $cache->load();

    $contents = [['path' => 'test.txt']];
    $cache->storeContents('', $contents);
    $this->assertTrue($cache->has('test.txt'));

    $this->assertSame('test.txt', key($backend->get('flysystem')->data[0]));

    // Test loading.
    $cache = new DrupalFlysystemCache($backend, 'flysystem');
    $this->assertNull($cache->has('test.txt'));

    $cache->load();
    $this->assertTrue($cache->has('test.txt'));
    $this->assertFalse($cache->has('missing.txt'));
  }

}
