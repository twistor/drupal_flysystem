<?php

/**
 * @file
 * Contains \NoDrupal\Tests\flysystem\Unit\Routing\FlysystemRoutesTest
 */

namespace NoDrupal\Tests\flysystem\Unit\Routing;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StreamWrapper\LocalStream;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\Routing\FlysystemRoutes;
use Symfony\Component\Routing\Route;

/**
 * @coversDefaultClass \Drupal\flysystem\Routing\FlysystemRoutes
 * @group flysystem
 */
class FlysystemRoutesTest extends UnitTestCase {

  /**
   * @covers ::__construct
   * @covers ::create
   * @covers ::routes
   */
  public function testRoutes() {
    $container = new ContainerBuilder();

    $stream_wrapper = $this->prophesize(LocalStream::class);
    $stream_wrapper->getDirectoryPath()->willReturn('sites/default/files');

    $stream_wrapper_manager = $this->prophesize(StreamWrapperManagerInterface::class);
    $stream_wrapper_manager->getViaScheme('public')->willReturn($stream_wrapper->reveal());

    $module_handler = $this->prophesize(ModuleHandlerInterface::class);

    $container->set('stream_wrapper_manager', $stream_wrapper_manager->reveal());
    $container->set('module_handler', $module_handler->reveal());

    $router = FlysystemRoutes::create($container);

    $this->assertSame([], $router->routes());

    new Settings(['flysystem' => ['test' => ['driver' => 'local']]]);
    $this->assertSame([], $router->routes());
    new Settings(['flysystem' => ['test' => ['driver' => 'ftp']]]);
    $this->assertSame([], $router->routes());

    $schemes = [
      'test' => [
        'driver' => 'local',
        'public' => TRUE,
        'config' => [
          'public' => TRUE,
          'root' => 'sites/default/files',
        ],
      ],
    ];
    new Settings(['flysystem' => $schemes]);
    $this->assertSame([], $router->routes());

    $schemes['test']['config']['root'] = 'sites/default/files/flysystem';
    new Settings(['flysystem' => $schemes]);

    $expected = new Route(
      '/sites/default/files/flysystem/{scheme}',
      [
        '_controller' => 'Drupal\system\FileDownloadController::download',
      ],
      [
        '_access' => 'TRUE',
        'scheme' => '^[a-zA-Z0-9+.-]+$',
      ]
    );

    $routes = $router->routes();
    $this->assertSame(1, count($routes));
    $this->assertSame($expected->serialize(), $routes['flysystem.test.serve']->serialize());

    // Enable image module.
    $module_handler->moduleExists('image')->willReturn(TRUE);
    $routes = $router->routes();
    $this->assertSame(3, count($routes));
    $this->assertSame($expected->serialize(), $routes['flysystem.test.serve']->serialize());
  }

}
