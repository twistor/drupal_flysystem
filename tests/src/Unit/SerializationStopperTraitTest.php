<?php

/**
 * @file
 * Contains \Drupal\Tests\flysystem\Unit\SerializationStopperTraitTest.
 */

namespace Drupal\Tests\flysystem\Unit;

/**
 * @coversDefaultClass \Drupal\flysystem\SerializationStopperTrait
 * @group flysystem
 */
class SerializationStopperTraitTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers ::__sleep
   *
   * @expectedException \LogicException
   * @expectedExceptionMessage can not be serialized.
   */
  public function test() {
    $trait = $this->getMockForTrait('Drupal\flysystem\SerializationStopperTrait');
    serialize($trait);
  }

}
