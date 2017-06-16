<?php

namespace Drupal\flysystem\Routing;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\flysystem\FlysystemFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Defines a route subscriber to register a url for serving image styles.
 */
class FlysystemRoutes implements ContainerInjectionInterface {

  /**
   * The Flysystem factory.
   *
   * @var \Drupal\flysystem\FlysystemFactory
   */
  protected $factory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The stream wrapper manager service.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface
   */
  protected $streamWrapperManager;

  /**
   * Constructs a new FlysystemRoutes object.
   *
   * @param \Drupal\flysystem\FlysystemFactory $factory
   *   The Flysystem factory.
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $stream_wrapper_manager
   *   The stream wrapper manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(FlysystemFactory $factory, StreamWrapperManagerInterface $stream_wrapper_manager, ModuleHandlerInterface $module_handler) {
    $this->factory = $factory;
    $this->streamWrapperManager = $stream_wrapper_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('flysystem_factory'),
      $container->get('stream_wrapper_manager'),
      $container->get('module_handler')
    );
  }

  /**
   * Returns a list of route objects.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   An array of route objects.
   */
  public function routes() {
    $public_directory_path = $this->streamWrapperManager->getViaScheme('public')->getDirectoryPath();
    $routes = [];

    $all_settings = Settings::get('flysystem', []);

    foreach ($this->factory->getSchemes() as $scheme) {
      $settings = $all_settings[$scheme];

      if ($settings['driver'] !== 'local' || empty($settings['config']['public'])) {
        continue;
      }

      // If the root is the same as the public files directory, skip adding a
      // route.
      if ($settings['config']['root'] === $public_directory_path) {
        continue;
      }

      $routes['flysystem.' . $scheme . '.serve'] = new Route(
        '/' . $settings['config']['root'],
        [
          '_controller' => 'Drupal\system\FileDownloadController::download',
          '_disable_route_normalizer' => TRUE,
          'scheme' => $scheme,
        ],
        [
          '_access' => 'TRUE',
        ]
      );

      if ($this->moduleHandler->moduleExists('image')) {
        // Public image route.
        $routes['flysystem.' . $scheme . '.style_public'] = new Route(
          '/' . $settings['config']['root'] . '/styles/{image_style}/' . $scheme,
          [
            '_controller' => 'Drupal\image\Controller\ImageStyleDownloadController::deliver',
            '_disable_route_normalizer' => TRUE,
            'scheme' => $scheme,
          ],
          [
            '_access' => 'TRUE',
          ]
        );
      }
    }

    if ($this->moduleHandler->moduleExists('image')) {
      // Internal image rotue.
      $routes['flysystem.image_style'] = new Route(
        '/_flysystem/styles/{image_style}/{scheme}',
        [
          '_controller' => 'Drupal\image\Controller\ImageStyleDownloadController::deliver',
          '_disable_route_normalizer' => TRUE,
        ],
        [
          '_access' => 'TRUE',
          'scheme' => '^[a-zA-Z0-9+.-]+$',
        ]
      );
    }

    return $routes;
  }

}
