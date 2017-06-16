<?php

namespace Drupal\Tests\flysystem\Unit\Plugin;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Routing\UrlGenerator;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;

/**
 * @coversDefaultClass \Drupal\flysystem\Plugin\FlysystemUrlTrait
 * @group flysystem
 */
class FlysystemUrlTraitTest extends UnitTestCase {

  /**
   * @covers ::getExternalUrl
   * @covers ::getScheme
   * @covers ::getTarget
   */
  public function testGetExternalUrl() {
    $trait = $this->getMockForTrait(FlysystemUrlTrait::class);

    $url_generator = $this->prophesize(UrlGenerator::class);
    $url_generator->generateFromRoute(
      'flysystem.serve',
      ['scheme' => 'testscheme', 'filepath' => 'dir/file.txt'],
      ['absolute' => TRUE],
      FALSE)
      ->willReturn('download');

    $container = new ContainerBuilder();
    $container->set('url_generator', $url_generator->reveal());

    \Drupal::setContainer($container);

    $this->assertSame('download', $trait->getExternalUrl('testscheme://dir\file.txt'));
  }

}
