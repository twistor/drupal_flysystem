<?php

/**
 * @file
 * Contains \NoDrupal\Tests\flysystem\Unit\Flysystem\FtpTest.
 */

namespace NoDrupal\Tests\flysystem\Unit\Flysystem {

use Drupal\flysystem\Flysystem\Ftp;

/**
 * @coversDefaultClass \Drupal\flysystem\Flysystem\Ftp
 * @group flysystem
 */
class FtpTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers \Drupal\flysystem\Flysystem\Ftp
   */
  public function test() {
    if (!defined('FTP_BINARY')) {
      $this->markTestSkipped('The FTP_BINARY constant is not defined.');
    }

    $plugin = new Ftp(['host' => 'success']);
    $this->assertInstanceOf('League\Flysystem\Adapter\Ftp', $plugin->getAdapter());
    $this->assertTrue(is_array($plugin->ensure()));
    $this->assertSame(0, count($plugin->ensure()));

    // Test broken connection behavior.
    $plugin = new Ftp([]);
    $this->assertInstanceOf('Drupal\flysystem\Flysystem\Adapter\MissingAdapter', $plugin->getAdapter());
    $result = $plugin->ensure();
    $this->assertTrue(is_array($result));
    $this->assertSame(1, count($result));
    $this->assertSame(21, $result[0]['context']['%port']);
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
 * Stubs ftp_close().
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
 * Stubs ftp_pasv().
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

/**
 * Stubs ftp_systype().
 */
function ftp_systype() {
  return TRUE;
}

}
