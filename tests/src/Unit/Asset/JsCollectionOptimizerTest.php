<?php

namespace Drupal\Tests\flysystem\Unit\Asset {

use Drupal\Core\Asset\AssetCollectionGrouperInterface;
use Drupal\Core\Asset\CssOptimizer;
use Drupal\Core\Asset\JsOptimizer;
use Drupal\Core\State\StateInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\Asset\AssetDumper;
use Drupal\flysystem\Asset\CssCollectionOptimizer;
use Drupal\flysystem\Asset\JsCollectionOptimizer;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use org\bovigo\vfs\vfsStream;

/**
 * @coversDefaultClass \Drupal\flysystem\Asset\JsCollectionOptimizer
 * @group flysystem
 */
class JsCollectionOptimizerTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    if (!defined('REQUEST_TIME')) {
      define('REQUEST_TIME', time());
    }

    vfsStream::setup('flysystem');
    if (file_exists('vfs://flysystem/test.js')) {
      unlink('vfs://flysystem/test.js');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {

    if (file_exists('vfs://flysystem/test.js')) {
      unlink('vfs://flysystem/test.js');
    }

    parent::tearDown();
  }

  /**
   * @covers \Drupal\flysystem\Asset\JsCollectionOptimizer
   * @covers \Drupal\flysystem\Asset\CssCollectionOptimizer
   */
  public function test() {
    file_put_contents('vfs://flysystem/test.js', 'asdfasdf');
    touch('vfs://flysystem/test.js', REQUEST_TIME - 1000);

    $container = new ContainerBuilder();
    $container->set('config.factory', $this->getConfigFactoryStub([
      'system.performance' => ['stale_file_threshold' => 0],
    ]));

    \Drupal::setContainer($container);

    $grouper = $this->prophesize(AssetCollectionGrouperInterface::class);
    $dumper = new AssetDumper();
    $state = $this->getMock(StateInterface::class);

    $optimizer = new JsCollectionOptimizer($grouper->reveal(), new JsOptimizer(), $dumper, $state);

    $optimizer->deleteAll();
    $this->assertFalse(file_exists('vfs://flysystem/test.js'));


    file_put_contents('vfs://flysystem/test.js', 'asdfasdf');
    touch('vfs://flysystem/test.js', REQUEST_TIME - 1000);

    $optimizer = new CssCollectionOptimizer($grouper->reveal(), new CssOptimizer(), $dumper, $state);
    $optimizer->deleteAll();
    $this->assertFalse(file_exists('vfs://flysystem/test.js'));
  }

}
}

namespace {
  if (!function_exists('file_scan_directory')) {
    function file_scan_directory($dir, $mask, array $options) {
      $options['callback']('vfs://flysystem/test.js');
    }
  }

  if (!function_exists('file_unmanaged_delete')) {
    function file_unmanaged_delete($uri) {
      unlink($uri);
    }
  }
}
