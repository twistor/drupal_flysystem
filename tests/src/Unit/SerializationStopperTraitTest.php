<?php

namespace Drupal\Tests\flysystem\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\SerializationStopperTrait;

/**
 * @coversDefaultClass \Drupal\flysystem\SerializationStopperTrait
 * @group flysystem
 */
class SerializationStopperTraitTest extends UnitTestCase {

  /**
   * @covers ::__sleep
   *
   * @expectedException \LogicException
   * @expectedExceptionMessage can not be serialized.
   */
  public function test() {
    $trait = $this->getMockForTrait(SerializationStopperTrait::class);
    serialize($trait);
  }

}
