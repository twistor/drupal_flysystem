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
    if (!defined('FTP_BINARY')) {
      $this->markTestSkipped('The FTP_BINARY constant is not defined');
    }

    $plugin = new Ftp([]);
    $this->assertInstanceOf('League\Flysystem\Adapter\Ftp', $plugin->getAdapter());
    $this->assertTrue(is_array($plugin->ensure()));
    $this->assertSame(0, count($plugin->ensure()));
  }

}
