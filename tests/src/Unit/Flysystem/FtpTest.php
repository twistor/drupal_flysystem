<?php

namespace Drupal\Tests\flysystem\Unit\Flysystem {

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\Flysystem\Adapter\MissingAdapter;
use Drupal\flysystem\Flysystem\Ftp;
use League\Flysystem\Adapter\Ftp as LeagueFtp;

/**
 * @coversDefaultClass \Drupal\flysystem\Flysystem\Ftp
 * @group flysystem
 */
class FtpTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    if (!defined('FTP_BINARY')) {
      $this->markTestSkipped('The FTP_BINARY constant is not defined.');
    }
  }

  /**
   * @covers ::getAdapter
   * @covers ::__construct
   */
  public function testGetAdapterSuccess() {
    $plugin = new Ftp(['host' => 'success']);
    $this->assertInstanceOf(LeagueFtp::class, $plugin->getAdapter());
  }

  /**
   * @covers ::getAdapter
   * @covers ::__construct
   */
  public function testGetAdapterFails() {
    $plugin = new Ftp([]);
    $this->assertInstanceOf(MissingAdapter::class, $plugin->getAdapter());
  }

  /**
   * @covers ::ensure
   */
  public function testEnsureReturnsNoErrorsOnSuccess() {
    $result = (new Ftp(['host' => 'success']))->ensure();

    $this->assertSame(1, count($result));
    $this->assertSame(RfcLogLevel::INFO, $result[0]['severity']);
  }

  /**
   * @covers ::ensure
   */
  public function testEnsureReturnsErrors() {
    $plugin = new Ftp([]);
    $result = $plugin->ensure();
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

/**
 * Stubs ftp_raw().
 */
function ftp_raw() {
  return ['200'];
}

}
