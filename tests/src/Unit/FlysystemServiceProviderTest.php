<?php

/**
 * @file
 * Contains \Drupal\Tests\flysystem\Unit\FlysystemServiceProviderTest.
 */

namespace Drupal\Tests\flysystem\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Site\Settings;
use Drupal\flysystem\FlysystemServiceProvider;

/**
 * @coversDefaultClass \Drupal\flysystem\FlysystemServiceProvider
 * @group flysystem
 */
class FlysystemServiceProviderTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers \Drupal\flysystem\FlysystemServiceProvider
   */
  public function test() {
    $container = new ContainerBuilder();
    $provider = new FlysystemServiceProvider();

    // Test that nothing fails when the container is empty.
    new Settings([]);
    $provider->register($container);

    // Test that missing driver is skipped.
    new Settings(['flysystem' => ['testscheme' => []]]);
    $provider->register($container);
    $this->assertFalse($container->has('flysystem_stream_wrapper.testscheme'));

    // Test valid scheme configuration.
    new Settings(['flysystem' => ['testscheme' => ['driver' => 'whatever']]]);
    $provider->register($container);
    $this->assertTrue($container->has('flysystem_stream_wrapper.testscheme'));
    $this->assertSame('Drupal\flysystem\FlysystemBridge', $container->getDefinition('flysystem_stream_wrapper.testscheme')->getClass());
    $this->assertSame([['scheme' => 'testscheme']], $container->getDefinition('flysystem_stream_wrapper.testscheme')->getTag('stream_wrapper'));

    // Test swapping the asset dumper.
    $container->register('asset.js.dumper', 'Drupal\Core\Asset\AssetDumper');
    $provider->register($container);
    $this->assertSame('Drupal\Core\Asset\AssetDumper', $container->getDefinition('asset.js.dumper')->getClass());

    $container->register('asset.js.collection_optimizer', 'Drupal\Core\Asset\JsCollectionOptimizer');
    $provider->register($container);
    $this->assertSame('Drupal\Core\Asset\AssetDumper', $container->getDefinition('asset.js.dumper')->getClass());
    $this->assertSame('Drupal\Core\Asset\JsCollectionOptimizer', $container->getDefinition('asset.js.collection_optimizer')->getClass());

    // A successful swap.
    new Settings(['flysystem' => ['testscheme' => ['driver' => 'whatever', 'serve_js' => TRUE]]]);
    $provider->register($container);
    $this->assertSame('Drupal\flysystem\Asset\AssetDumper', $container->getDefinition('asset.js.dumper')->getClass());
    $this->assertSame('Drupal\flysystem\Asset\JsCollectionOptimizer', $container->getDefinition('asset.js.collection_optimizer')->getClass());
  }

}
