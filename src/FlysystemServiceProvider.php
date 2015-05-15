<?php

/**
 * @file
 * Contains \Drupal\flysystem\FlysystemServiceProvider.
 */

namespace Drupal\flysystem;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Drupal\flysystem\RegisterStreamWrappersPass;

/**
 * Flysystem dependency injection container.
 */
class FlysystemServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    // Add a compiler pass for adding stream wrappers.
    $container->addCompilerPass(new RegisterStreamWrappersPass());
  }

}
