<?php

/**
 * @file
 * Contains \NoDrupal\Tests\flysystem\Unit\Controller\DownloadControllerTest.
 */

namespace NoDrupal\Tests\flysystem\Unit\Controller;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\Controller\DownloadController;
use Prophecy\Argument;
use org\bovigo\vfs\vfsStream;

/**
 * Tests for \Drupal\flysystem\Controller\DownloadController.
 *
 * @group Flysystem
 * @covers \Drupal\flysystem\Controller\DownloadController
 */
class DownloadControllerTest extends UnitTestCase {

  protected $factory;

  protected $guesser;

  public function setUp() {
    parent::setUp();

    vfsStream::setup('fly');

    // Mock filesystem.
    $filesystem = $this->prophesize('League\Flysystem\FilesystemInterface');
    $filesystem->has(Argument::type('string'))->will(function($args) {
      return file_exists('vfs://' . $args[0]);
    });

    $filesystem->getSize(Argument::type('string'))->will(function($args) {
      return filesize('vfs://' . $args[0]);
    });

    // Mock factory.
    $factory = $this->prophesize('Drupal\flysystem\FlysystemFactory');
    $factory->getFilesystem('vfs')->willReturn($filesystem->reveal());
    $this->factory = $factory->reveal();

    $guesser = $this->prophesize('Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface');
    $guesser->guess(Argument::type('string'))->willReturn('text/plain');
    $this->guesser = $guesser->reveal();
  }

  public function testCreate() {
    $container = new ContainerBuilder();
    $container->set('file.mime_type.guesser.extension', $this->guesser);
    $container->set('flysystem_factory', $this->factory);
    $this->assertInstanceOf('Drupal\flysystem\Controller\DownloadController', DownloadController::create($container));
  }

  public function testServe() {
    $controller = new DownloadController($this->factory, $this->guesser);
    file_put_contents('vfs://fly/file.txt', '0123456789');
    $response = $controller->serve('vfs', 'fly/file.txt');
    $this->assertSame(200, $response->getStatusCode());
    $this->assertSame('0123456789', file_get_contents((string) $response->getFile()));
  }

  /**
   * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function testServeMissing() {
    (new DownloadController($this->factory, $this->guesser))->serve('vfs', 'fly/missing.txt');
  }

}
