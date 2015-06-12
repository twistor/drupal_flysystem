<?php

/**
 * @file
 * Contains \NoDrupal\Tests\flysystem\Unit\Flysystem\MissingTest.
 */

namespace NoDrupal\Tests\flysystem\Unit\Flysystem;

use Drupal\flysystem\Flysystem\Missing;

/**
 * @coversDefaultClass \Drupal\flysystem\Flysystem\Missing
 * @group flysystem
 */
class MissingTest extends \PHPUnit_Framework_TestCase {

  public function test() {
    $plugin = new Missing([]);
    $this->assertInstanceOf('Drupal\flysystem\Flysystem\Adapter\MissingAdapter', $plugin->getAdapter());
    $this->assertTrue(is_array($plugin->ensure()));
    $this->assertSame(1, count($plugin->ensure()));
    $this->assertSame('', $plugin->getExternalUrl('asdf'));
  }

}
