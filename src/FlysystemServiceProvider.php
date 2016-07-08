<?php

namespace Drupal\flysystem;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Drupal\Core\Site\Settings;
use Drupal\flysystem\Asset\SchemeExtensionTrait;

/**
 * Flysystem dependency injection container.
 */
class FlysystemServiceProvider implements ServiceProviderInterface {

  use SchemeExtensionTrait;

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {

    $this->swapDumper($container, 'js');
    $this->swapDumper($container, 'css');

    foreach (Settings::get('flysystem', []) as $scheme => $settings) {

      // Just some sanity checking, so things don't explode.
      if (empty($settings['driver'])) {
        continue;
      }

      $container
        ->register('flysystem_stream_wrapper.' . $scheme, 'Drupal\flysystem\FlysystemBridge')
        ->addTag('stream_wrapper', ['scheme' => $scheme]);

      // Register the path processors for local files.
      if ($settings['driver'] === 'local' && !empty($settings['config']['public'])) {
        $container
          ->register('flysystem.' . $scheme . '.path_processor', 'Drupal\flysystem\PathProcessor\LocalPathProcessor')
          ->addTag('path_processor_inbound', ['priority' => 400])
          ->addArgument($scheme);
      }
    }
  }

  /**
   * Swaps the js/css dumper if a scheme is configured to serve it.
   *
   * @param \Drupal\Core\DependencyInjection\ContainerBuilder $container
   *   The container.
   * @param string $extension
   *   The file extension to swap.
   */
  protected function swapDumper(ContainerBuilder $container, $extension) {
    if (!$container->has('asset.' . $extension . '.dumper')) {
      return;
    }

    if (!$container->has('asset.' . $extension . '.collection_optimizer')) {
      return;
    }

    // Don't rewrite if there's nothing to change.
    if ($this->getSchemeForExtension($extension) === 'public') {
      return;
    }

    $container
      ->getDefinition('asset.' . $extension . '.dumper')
      ->setClass('Drupal\flysystem\Asset\AssetDumper');
    $container
      ->getDefinition('asset.' . $extension . '.collection_optimizer')
      ->setClass('Drupal\flysystem\Asset\\' . ucfirst($extension) . 'CollectionOptimizer');

    if ($extension === 'css') {
      $container
        ->getDefinition('asset.css.optimizer')
        ->setClass('Drupal\flysystem\Asset\CssOptimizer');
    }
  }

}
