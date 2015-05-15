<?php

/**
 * @file
 * Contains \Drupal\flysystem\FlysystemServiceProvider.
 */

namespace Drupal\flysystem;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Drupal\Core\Site\Settings;

/**
 * Flysystem dependency injection container.
 */
class FlysystemServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    foreach (Settings::get('flysystem', []) as $scheme => $settings) {
      $container
        ->register('flysystem_stream_wrapper.' . $scheme, 'Drupal\flysystem\FlysystemBridge')
        ->addTag('stream_wrapper', ['scheme' => $scheme]);
    }
  }

}
