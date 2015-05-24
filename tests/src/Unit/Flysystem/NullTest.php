<?php

/**
 * @file
 * Contains \Drupal\Tests\flysystem\Unit\Flysystem\NullTest.
 */

namespace NoDrupal\Tests\flysystem\Unit\Flysystem;

use Drupal\flysystem\Flysystem\Null;

/**
 * Tests for \Drupal\flysystem\Flysystem\Null.
 *
 * @group Flysystem
 * @covers \Drupal\flysystem\Flysystem\Null
 */
class NullTest extends \PHPUnit_Framework_TestCase {

  public function test() {
    $plugin = new Null([]);
    $this->assertInstanceOf('League\Flysystem\Adapter\NullAdapter', $plugin->getAdapter());
    $this->assertTrue(is_array($plugin->ensure()));
    $this->assertSame(0, count($plugin->ensure()));
  }

}
