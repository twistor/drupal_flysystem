<?php

namespace Drupal\Tests\flysystem\Unit\Flysystem;

use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\Flysystem\Adapter\MissingAdapter;
use Drupal\flysystem\Flysystem\Missing;

/**
 * @coversDefaultClass \Drupal\flysystem\Flysystem\Missing
 * @group flysystem
 */
class MissingTest extends UnitTestCase {

  /**
   * @covers \Drupal\flysystem\Flysystem\Missing
   */
  public function test() {
    $plugin = new Missing([]);
    $this->assertInstanceOf(MissingAdapter::class, $plugin->getAdapter());
    $this->assertTrue(is_array($plugin->ensure()));
    $this->assertSame(1, count($plugin->ensure()));
    $this->assertSame('', $plugin->getExternalUrl('asdf'));
  }

}
