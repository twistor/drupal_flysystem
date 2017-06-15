<?php

namespace Drupal\Tests\flysystem\Unit;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Cache\NullBackend;
use Drupal\Core\File\FileSystemInterface as CoreFileSystemInterface;
use Drupal\Core\Site\Settings;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\FlysystemFactory;
use Drupal\flysystem\Flysystem\Adapter\DrupalCacheAdapter;
use Drupal\flysystem\Flysystem\Adapter\MissingAdapter;
use Drupal\flysystem\Flysystem\Missing;
use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\Replicate\ReplicateAdapter;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @coversDefaultClass \Drupal\flysystem\FlysystemFactory
 * @group flysystem
 */
class FlysystemFactoryTest extends UnitTestCase {

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $filesystem;

  /**
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $plugin;

  /**
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $plguinManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->cache = new NullBackend('bin');
    $this->eventDispatcher = $this->getMock(EventDispatcherInterface::class);

    $this->plugin_manager = $this->prophesize(PluginManagerInterface::class);
    $this->plugin = $this->prophesize(FlysystemPluginInterface::class);
    $this->plugin->getAdapter()->willReturn(new NullAdapter());

    $this->plugin_manager->createInstance('testdriver', [])->willReturn($this->plugin->reveal());
    $this->plugin_manager->createInstance('', [])->willReturn(new Missing());

    $this->filesystem = $this->prophesize(CoreFileSystemInterface::class);
    $this->filesystem->validScheme(Argument::type('string'))->willReturn(TRUE);
  }

  /**
   * @covers ::getFilesystem
   * @covers ::__construct
   * @covers ::getAdapter
   * @covers ::getSettings
   * @covers ::getPlugin
   */
  public function testGetFilesystemReturnsValidFilesystem() {
    new Settings([
      'flysystem' => [
        'testscheme' => ['driver' => 'testdriver'],
      ],
    ]);

    $factory = $this->getFactory();

    $this->assertInstanceOf(FilesystemInterface::class, $factory->getFilesystem('testscheme'));
    $this->assertInstanceOf(NullAdapter::class, $factory->getFilesystem('testscheme')->getAdapter());
  }

  /**
   * @covers ::getFilesystem
   */
  public function testGetFilesystemReturnsMissingFilesystem() {
    new Settings([]);
    $factory = $this->getFactory();
    $this->assertInstanceOf(MissingAdapter::class, $factory->getFilesystem('testscheme')->getAdapter());
  }

  /**
   * @covers ::getFilesystem
   * @covers ::getAdapter
   */
  public function testGetFilesystemReturnsCachedAdapter() {
    new Settings([
      'flysystem' => [
        'testscheme' => ['driver' => 'testdriver' , 'cache' => TRUE],
      ],
    ]);

    $factory = $this->getFactory();
    $this->assertInstanceOf(DrupalCacheAdapter::class, $factory->getFilesystem('testscheme')->getAdapter());
  }

  /**
   * @covers ::getFilesystem
   * @covers ::getAdapter
   */
  public function testGetFilesystemReturnsReplicateAdapter() {
    // Test replicate.
    $this->plugin_manager->createInstance('wrapped', [])->willReturn($this->plugin->reveal());

    new Settings([
      'flysystem' => [
        'testscheme' => ['driver' => 'testdriver' , 'replicate' => 'wrapped'],
        'wrapped' => ['driver' => 'testdriver'],
      ],
    ]);

    $factory = $this->getFactory();
    $this->assertInstanceOf(ReplicateAdapter::class, $factory->getFilesystem('testscheme')->getAdapter());
  }

  /**
   * @covers ::getSchemes
   * @covers ::__construct
   */
  public function testGetSchemesFiltersInvalidSchemes() {
    new Settings([
      'flysystem' => [
        'testscheme' => ['driver' => 'testdriver'],
        'invalidscheme' => ['driver' => 'testdriver'],
      ],
    ]);

    $this->filesystem->validScheme('invalidscheme')->willReturn(FALSE);

    $this->assertSame(['testscheme'], $this->getFactory()->getSchemes());
  }

  /**
   * @covers ::getSchemes
   */
  public function testGetSchemesHandlesNoSchemes() {
    new Settings([]);
    $this->assertSame([], $this->getFactory()->getSchemes());
  }

  /**
   * @covers ::ensure
   */
  public function testEnsureReturnsErrors() {
    new Settings([
      'flysystem' => [
        'testscheme' => ['driver' => 'testdriver'],
      ],
    ]);

    $this->plugin->ensure(FALSE)->willReturn([[
      'severity' => 'bad',
      'message' => 'Something bad',
      'context' => [],
    ],
    ]);

    $errors = $this->getFactory()->ensure();

    $this->assertSame('Something bad', $errors['testscheme'][0]['message']);
  }

  /**
   * @return \Drupal\flysystem\FlysystemFactory
   */
  protected function getFactory() {
    return new FlysystemFactory(
      $this->plugin_manager->reveal(),
      $this->filesystem->reveal(),
      $this->cache,
      $this->eventDispatcher
    );
  }

}
