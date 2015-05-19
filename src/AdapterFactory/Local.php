<?php

/**
 * @file
 * Contains \Drupal\flysystem\AdapterFactory\Local.
 */

namespace Drupal\flysystem\AdapterFactory;

use League\Flysystem\Adapter\Local as LocalAdapter;

class Local implements AdapterFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public static function canRegister() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(array $config) {
    return new LocalAdapter($config['root']);
  }

}
