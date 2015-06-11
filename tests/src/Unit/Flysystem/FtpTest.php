<?php

/**
 * @file
 * Contains \Drupal\Tests\flysystem\Unit\Flysystem\FtpTest.
 */

namespace NoDrupal\Tests\flysystem\Unit\Flysystem {

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

    $plugin = new Ftp(['host' => 'success']);
    $this->assertInstanceOf('League\Flysystem\Adapter\Ftp', $plugin->getAdapter());
    $this->assertTrue(is_array($plugin->ensure()));
    $this->assertSame(0, count($plugin->ensure()));

    // Test broken connection behavior.
    $plugin = new Ftp([]);
    $this->assertInstanceOf('Drupal\flysystem\Flysystem\Adapter\MissingAdapter', $plugin->getAdapter());
    $this->assertTrue(is_array($plugin->ensure()));
    $this->assertSame(1, count($plugin->ensure()));
  }

}
}

namespace League\Flysystem\Adapter {

/**
 * Stubs ftp_chdir().
 */
function ftp_chdir() {
  return TRUE;
}

/**
 * Stubs ftp_connect().
 */
function ftp_close() {
}

/**
 * Stubs ftp_connect().
 */
function ftp_connect($host) {
  return $host === 'success';
}

/**
 * Stubs ftp_login().
 */
function ftp_login() {
  return TRUE;
}

/**
 * Stubs ftp_connect().
 */
function ftp_pasv() {
  return TRUE;
}

/**
 * Stubs ftp_pwd().
 */
function ftp_pwd() {
  return '';
}

}
