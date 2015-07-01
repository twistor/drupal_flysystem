<?php

/**
 * @file
 * Contains \NoDrupal\Tests\flysystem\Unit\FlysystemFactoryTest.
 */

namespace NoDrupal\Tests\flysystem\Unit;

use Drupal\Core\Cache\NullBackend;
use Drupal\Core\Site\Settings;
use Drupal\flysystem\FlysystemFactory;
use League\Flysystem\Adapter\NullAdapter;

/**
 * @coversDefaultClass \Drupal\flysystem\FlysystemFactory
 * @group flysystem
 */
class FlysystemFactoryTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers \Drupal\flysystem\FlysystemFactory
   */
  public function test() {
    $cache = new NullBackend('bin');
    $logger = $this->getMock('Psr\Log\LoggerInterface');

    $plugin_manager = $this->prophesize('Drupal\Component\Plugin\PluginManagerInterface');
    $plugin = $this->prophesize('Drupal\flysystem\Plugin\FlysystemPluginInterface');
    $plugin->getAdapter()->willReturn(new NullAdapter());

    $plugin_manager->createInstance('testdriver', [])->willReturn($plugin->reveal());

    $settings = new Settings([
      'flysystem' => [
        'testscheme' => ['driver' => 'testdriver'],
      ],
    ]);

    $factory = new FlysystemFactory($plugin_manager->reveal(), $cache,  $settings, $logger);

    $this->assertInstanceOf('League\Flysystem\FilesystemInterface', $factory->getFilesystem('testscheme'));
    $this->assertInstanceOf('League\Flysystem\Adapter\NullAdapter', $factory->getFilesystem('testscheme')->getAdapter());

    // Test cache wrapping.
    $settings = new Settings([
      'flysystem' => [
        'testscheme' => ['driver' => 'testdriver' , 'cache' => TRUE],
      ],
    ]);

    $factory = new FlysystemFactory($plugin_manager->reveal(), $cache,  $settings, $logger);
    $this->assertInstanceOf('League\Flysystem\FilesystemInterface', $factory->getFilesystem('testscheme'));
    $this->assertInstanceOf('League\Flysystem\Cached\CachedAdapter', $factory->getFilesystem('testscheme')->getAdapter());

    // Test replicate.
    $plugin_manager->createInstance('wrapped', [])->willReturn($plugin->reveal());

    $settings = new Settings([
      'flysystem' => [
        'testscheme' => ['driver' => 'testdriver' , 'replicate' => 'wrapped'],
        'wrapped' => ['driver' => 'testdriver'],
      ],
    ]);
    $factory = new FlysystemFactory($plugin_manager->reveal(), $cache,  $settings, $logger);
    $this->assertInstanceOf('League\Flysystem\FilesystemInterface', $factory->getFilesystem('testscheme'));
    $this->assertInstanceOf('League\Flysystem\Replicate\ReplicateAdapter', $factory->getFilesystem('testscheme')->getAdapter());

    // Test ensure.
    $plugin->ensure(FALSE)->willReturn([[
      'severity' => 'bad',
      'message' => 'Something bad',
      'context' => [],
    ]]);
    $errors = $factory->ensure();
    $this->assertSame('Something bad', $errors['testscheme'][0]['message']);
  }

}
