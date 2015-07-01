<?php

/**
 * @file
 * Contains \Drupal\Tests\flysystem\Unit\ModuleFunctionsTest.
 */

namespace Drupal\Tests\flysystem\Unit;

use Drupal\Core\Site\Settings;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use org\bovigo\vfs\vfsStream;

/**
 * Tests module functions.
 *
 * @group flysystem
 */
class ModuleFunctionsTest extends UnitTestCase {

  /**
   * The Flysystem factory prophecy object.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $factory;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    vfsStream::setup('module_file');

    require_once dirname(dirname(dirname(__DIR__))) . '/flysystem.module';

    $this->factory = $this->prophesize('Drupal\flysystem\FlysystemFactory');

    $file_system_helper = $this->prophesize('Drupal\Core\File\FileSystemInterface');
    $file_system_helper->uriScheme(Argument::type('string'))->will(function ($uri) {
      list($scheme) = explode('://', $uri[0]);
      return $scheme;
    });

    $guesser = $this->prophesize('Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface');
    $guesser->guess(Argument::type('string'))->willReturn('txt/flysystem');

    $container = new ContainerBuilder();
    $container->set('file_system', $file_system_helper->reveal());
    $container->set('flysystem_factory', $this->factory->reveal());
    $container->set('file.mime_type.guesser.extension', $guesser->reveal());

    \Drupal::setContainer($container);
  }

  /**
   * Tests flysystem_cron().
   */
  public function testFlysystemCron() {
    $this->factory->ensure()->shouldBeCalled();
    flysystem_cron();
  }

  /**
   * Tests flysystem_rebuild().
   */
  public function testFlysystemRebuild() {
    $this->factory->ensure()->shouldBeCalled();
    flysystem_rebuild();
  }

  /**
   * Tests flysystem_file_download().
   */
  public function testFlysystemFileDownload() {
    new Settings(['flysystem' => []]);

    $this->assertNull(flysystem_file_download('vfs://module_file/file.txt'));

    new Settings(['flysystem' => ['vfs' => ['driver' => 'testdriver']]]);

    file_put_contents('vfs://module_file/file.txt', '1234');

    $return = flysystem_file_download('vfs://module_file/file.txt');

    $this->assertSame(2, count($return));
    $this->assertSame('txt/flysystem', $return['Content-Type']);
    $this->assertSame(4, $return['Content-Length']);
  }

}
