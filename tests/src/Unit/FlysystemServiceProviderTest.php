<?php

namespace Drupal\Tests\flysystem\Unit;

use Drupal\Core\Asset\AssetDumper;
use Drupal\Core\Asset\CssCollectionOptimizer;
use Drupal\Core\Asset\CssOptimizer;
use Drupal\Core\Asset\JsCollectionOptimizer;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Site\Settings;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\Asset\AssetDumper as FlysystemAssetDumper;
use Drupal\flysystem\Asset\CssCollectionOptimizer as FlysystemCssCollectionOptimizer;
use Drupal\flysystem\Asset\CssOptimizer as FlysystemCssOptimizer;
use Drupal\flysystem\Asset\JsCollectionOptimizer as FlysystemJsCollectionOptimizer;
use Drupal\flysystem\FlysystemBridge;
use Drupal\flysystem\FlysystemServiceProvider;
use Drupal\flysystem\PathProcessor\LocalPathProcessor;

/**
 * @coversDefaultClass \Drupal\flysystem\FlysystemServiceProvider
 * @group flysystem
 */
class FlysystemServiceProviderTest extends UnitTestCase {

  /**
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->container = new ContainerBuilder();
  }

  /**
   * @covers ::register
   */
  public function testNothingFailsIfContainerIsEmpty() {
    new Settings([]);
    (new FlysystemServiceProvider())->register($this->container);
  }

  /**
   * @covers ::register
   */
  public function testMissingDriverIsSkipped() {
    new Settings(['flysystem' => ['testscheme' => []]]);

    (new FlysystemServiceProvider())->register($this->container);

    $this->assertFalse($this->container->has('flysystem_stream_wrapper.testscheme'));
  }

  /**
   * @covers ::register
   */
  public function testValidSchemeConfiguration() {
    new Settings(['flysystem' => ['testscheme' => ['driver' => 'whatever']]]);

    (new FlysystemServiceProvider())->register($this->container);

    $this->assertTrue($this->container->has('flysystem_stream_wrapper.testscheme'));
    $this->assertSame(FlysystemBridge::class, $this->container->getDefinition('flysystem_stream_wrapper.testscheme')->getClass());
    $this->assertSame([['scheme' => 'testscheme']], $this->container->getDefinition('flysystem_stream_wrapper.testscheme')->getTag('stream_wrapper'));
  }

  /**
   * @covers ::register
   */
  public function testLocalRouteProviderGetsAdded() {
    new Settings([
      'flysystem' => [
        'testscheme' => ['driver' => 'local', 'config' => ['public' => TRUE]]],
    ]);

    (new FlysystemServiceProvider())->register($this->container);
    $this->assertSame(LocalPathProcessor::class, $this->container->getDefinition('flysystem.testscheme.path_processor')->getClass());
  }

  /**
   * @covers \Drupal\flysystem\FlysystemServiceProvider
   */
  public function test() {
    // Test swapping the asset dumper.
    $this->container->register('asset.js.dumper', AssetDumper::class);
    (new FlysystemServiceProvider())->register($this->container);
    $this->assertSame(AssetDumper::class, $this->container->getDefinition('asset.js.dumper')->getClass());

    $this->container->register('asset.js.collection_optimizer', JsCollectionOptimizer::class);
    (new FlysystemServiceProvider())->register($this->container);
    $this->assertSame(AssetDumper::class, $this->container->getDefinition('asset.js.dumper')->getClass());
    $this->assertSame(JsCollectionOptimizer::class, $this->container->getDefinition('asset.js.collection_optimizer')->getClass());

    // A successful swap.
    new Settings(['flysystem' => ['testscheme' => ['driver' => 'whatever', 'serve_js' => TRUE]]]);
    (new FlysystemServiceProvider())->register($this->container);
    $this->assertSame(FlysystemAssetDumper::class, $this->container->getDefinition('asset.js.dumper')->getClass());
    $this->assertSame(FlysystemJsCollectionOptimizer::class, $this->container->getDefinition('asset.js.collection_optimizer')->getClass());
  }

  /**
   * @covers \Drupal\flysystem\FlysystemServiceProvider
   */
  public function testSwappingCssServices() {
    // Test swapping the asset dumper.
    $this->container->register('asset.css.dumper', AssetDumper::class);
    $this->container->register('asset.css.collection_optimizer', CssCollectionOptimizer::class);
    $this->container->register('asset.css.optimizer', CssOptimizer::class);

    new Settings(['flysystem' => ['testscheme' => ['driver' => 'whatever', 'serve_css' => TRUE]]]);

    (new FlysystemServiceProvider())->register($this->container);

    $this->assertSame(FlysystemAssetDumper::class, $this->container->getDefinition('asset.css.dumper')->getClass());
    $this->assertSame(FlysystemCssCollectionOptimizer::class, $this->container->getDefinition('asset.css.collection_optimizer')->getClass());
    $this->assertSame(FlysystemCssOptimizer::class, $this->container->getDefinition('asset.css.optimizer')->getClass());
  }

}
