<?php

/**
 * @file
 * Contains \Drupal\Tests\flysystem\Unit\Flysystem\NullPluginTest.
 */

namespace NoDrupal\Tests\flysystem\Unit\Flysystem;

use Drupal\flysystem\Flysystem\NullPlugin;

/**
 * Tests for \Drupal\flysystem\Flysystem\NullPlugin.
 *
 * @group Flysystem
 * @covers \Drupal\flysystem\Flysystem\NullPlugin
 */
class NullPluginTest extends \PHPUnit_Framework_TestCase {

  public function test() {
    $plugin = new NullPlugin([]);
    $this->assertInstanceOf('League\Flysystem\Adapter\NullAdapter', $plugin->getAdapter());
    $this->assertTrue(is_array($plugin->ensure()));
    $this->assertSame(0, count($plugin->ensure()));
  }

}
