<?php

namespace Drupal\Tests\flysystem\Unit\Logger;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\Logger\Convert;

/**
 * @coversDefaultClass \Drupal\flysystem\Logger\Convert
 * @group flysystem
 */
class ConvertTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $consts = [
      'REQUIREMENT_INFO' => -1,
      'REQUIREMENT_OK' => 0,
      'REQUIREMENT_WARNING' => 1,
      'REQUIREMENT_ERROR' => 2,
    ];

    foreach ($consts as $const => $value) {
      if (!defined($const)) {
        define($const, $value);
      }
    }
  }

  /**
   * @covers ::rfcToHookRequirements
   */
  public function test() {
    $this->assertSame(REQUIREMENT_ERROR, Convert::rfcToHookRequirements(RfcLogLevel::EMERGENCY));
    $this->assertSame(REQUIREMENT_ERROR, Convert::rfcToHookRequirements(RfcLogLevel::ERROR));
    $this->assertSame(REQUIREMENT_WARNING, Convert::rfcToHookRequirements(RfcLogLevel::WARNING));
    $this->assertSame(REQUIREMENT_INFO, Convert::rfcToHookRequirements(RfcLogLevel::NOTICE));
    $this->assertSame(REQUIREMENT_INFO, Convert::rfcToHookRequirements(RfcLogLevel::INFO));
    $this->assertSame(REQUIREMENT_OK, Convert::rfcToHookRequirements(RfcLogLevel::DEBUG));
  }

}
