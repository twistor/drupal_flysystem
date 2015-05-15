<?php

/**
 * @file
 * Contains \Drupal\flysystem\RegisterStreamWrappersPass.
 */

namespace Drupal\flysystem;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds 'flysystem_stream_wrapper' tagged services.
 */
class RegisterStreamWrappersPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    if (!$container->hasDefinition('stream_wrapper_manager')) {
      return;
    }

    $stream_wrapper_manager = $container->getDefinition('stream_wrapper_manager');

    foreach ($container->findTaggedServiceIds('flysystem_stream_wrapper') as $id => $attributes) {
      $class = $container->getDefinition($id)->getClass();
      $scheme = $attributes[0]['scheme'];

      debug($scheme);

      $stream_wrapper_manager->addMethodCall('addStreamWrapper', [$id, $class, $scheme]);
    }
  }

}
