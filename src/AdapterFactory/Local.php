<?php

/**
 * @file
 * Contains \Drupal\flysystem\AdapterFactory\Local.
 */

namespace Drupal\flysystem\AdapterFactory;

use League\Flysystem\Adapter\Local;

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
    $config += ['prefix' => ''];
    $adapter = new Local($config['root']);
    $adapter->setPathPrefix($config['prefix']);

    return $adapter;
  }

}
