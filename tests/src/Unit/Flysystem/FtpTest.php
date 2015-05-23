<?php

/**
 * @file
 * Contains \Drupal\Tests\flysystem\Unit\Flysystem\FtpTest.
 */

namespace NoDrupal\Tests\flysystem\Unit\Flysystem;

use Drupal\flysystem\Flysystem\Ftp;

/**
 * Tests for \Drupal\flysystem\Flysystem\Ftp.
 *
 * @group Flysystem
 * @covers \Drupal\flysystem\Flysystem\Ftp
 */
class FtpTest extends \PHPUnit_Framework_TestCase {

  public function test() {
    $plugin = new Ftp([]);
    $this->assertInstanceOf('League\Flysystem\Adapter\Ftp', $plugin->getAdapter());
  }

}
