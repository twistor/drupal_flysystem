<?php

/**
 * @file
 * Contains \Drupal\Tests\flysystem\Unit\Plugin\FlysystemUrlTraitTest.
 */

namespace Drupal\Tests\flysystem\Unit\Plugin;

use Drupal\Core\Cache\NullBackend;
use Drupal\Core\Site\Settings;
use Drupal\flysystem\FlysystemUrlTrait;
use League\Flysystem\Adapter\NullAdapter;

/**
 * @coversDefaultClass \Drupal\flysystem\Plugin\FlysystemUrlTrait
 * @group flysystem
 */
class FlysystemUrlTraitTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers \Drupal\flysystem\Plugin\FlysystemUrlTrait
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
