<?php

/**
 * @file
 * Contains \NoDrupal\Tests\flysystem\Unit\Plugin\FlysystemUrlTraitTest.
 */

namespace NoDrupal\Tests\flysystem\Unit\Plugin;

/**
 * @coversDefaultClass \Drupal\flysystem\Plugin\FlysystemUrlTrait
 * @group flysystem
 */
class FlysystemUrlTraitTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers ::getExternalUrl
   * @covers ::getScheme
   * @covers ::getTarget
   */
  public function testGetExternalUrl() {
    $trait = $this->getMockForTrait('Drupal\flysystem\Plugin\FlysystemUrlTrait');
    $url_generator = $this->prophesize('Drupal\Core\Routing\UrlGenerator');
    $url_generator->generateFromRoute(
      'flysystem.serve',
      ['scheme' => 'testscheme', 'filepath' => 'dir/file.txt'],
      ['absolute' => TRUE])
      ->willReturn('download');

    $trait->setUrlGenerator($url_generator->reveal());

    $this->assertSame('download', $trait->getExternalUrl('testscheme://dir\file.txt'));
  }

}
