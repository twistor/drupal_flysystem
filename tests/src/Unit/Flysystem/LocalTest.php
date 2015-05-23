<?php

/**
 * @file
 * Contains \Drupal\Tests\flysystem\Unit\Flysystem\LocalTest.
 */

namespace NoDrupal\Tests\flysystem\Unit\Flysystem;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Site\Settings;
use Drupal\flysystem\Flysystem\Local;
use Prophecy\Argument;

/**
 * Tests for \Drupal\flysystem\Flysystem\Local.
 *
 * @group Flysystem
 * @covers \Drupal\flysystem\Flysystem\Local
 */
class LocalTest extends \PHPUnit_Framework_TestCase {

  public function setUp() {
    $this->tmpdir = sys_get_temp_dir();
    mkdir($this->tmpdir . '/flysystem');
    mkdir($this->tmpdir . '/flysystem2');
  }

  public function tearDown() {
    rmdir($this->tmpdir . '/flysystem');
    rmdir($this->tmpdir . '/flysystem2');
  }

  public function testCreate() {
    $kernel = $this->prophesize('Drupal\Core\DrupalKernelInterface');
    $kernel->getSitePath()->willReturn('');

    $container = new ContainerBuilder();
    $container->set('settings', new Settings([
      'file_public_path' => $this->tmpdir . '/flysystem',
    ]));

    $container->set('kernel', $kernel->reveal());

    $configuration = ['root' => $this->tmpdir . '/flysystem2'];

    $plugin = Local::create($container, $configuration, NULL, NULL);
    $this->assertInstanceOf('Drupal\flysystem\Plugin\FlysystemPluginInterface', $plugin);
  }

  public function testGetAdapter() {
    $local = new Local($this->tmpdir, $this->tmpdir . '/flysystem');
    $this->assertInstanceOf('League\Flysystem\Adapter\Local', $local->getAdapter());
  }

  public function testGetExternalUrl() {
    $url_generator = $this->prophesize('Drupal\Core\Routing\UrlGenerator');
    $url_generator->generateFromRoute(Argument::cetera())->willReturn('download');
    $url_generator->generateFromPath(Argument::cetera())->willReturn('serve');

    // Public and root are different.
    $local = new Local($this->tmpdir . '/flysystem', __DIR__);
    $local->setUrlGenerator($url_generator->reveal());
    $this->assertSame('download', $local->getExternalUrl('test://file.txt'));

    // Edge case.
    $local = new Local($this->tmpdir . '/flysystem', $this->tmpdir . '/flysystem2');
    $local->setUrlGenerator($url_generator->reveal());
    $this->assertSame('download', $local->getExternalUrl('test://file.txt'));

    // Public is invalid.
    $local = new Local('asdfasdf', $this->tmpdir . '/flysystem2');
    $local->setUrlGenerator($url_generator->reveal());
    $this->assertSame('download', $local->getExternalUrl('test://file.txt'));

    // Public and root are the same.
    $local = new Local(__DIR__, __DIR__);
    $local->setUrlGenerator($url_generator->reveal());
    $this->assertSame('serve', $local->getExternalUrl('test://file.txt'));

    // Root is inside public.
    $local = new Local($this->tmpdir, $this->tmpdir . '/flysystem');
    $local->setUrlGenerator($url_generator->reveal());
    $this->assertSame('serve', $local->getExternalUrl('test://file.txt'));
  }

}
