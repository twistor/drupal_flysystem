<?php

/**
 * @file
 * Contains \Drupal\flysystem\AdapterFactory\AdapterFactoryInterface.
 */

namespace Drupal\flysystem\AdapterFactory;

/**
 * Factory interface for Flysystem adapters.
 */
interface AdapterFactoryInterface {

  /**
   * Determines whether the adapter is usable.
   *
   * @return bool
   *   True if the adapter is usable, false if not.
   */
  public static function canRegister();

  /**
   * Adapter factory function.
   *
   * @param array $config
   *   The configuration array. Ususally taken from settings.php.
   *
   * @return \League\Flysystem\AdapterInterface
   *   A new adapter instance.
   */
  public static function create(array $config);

}
