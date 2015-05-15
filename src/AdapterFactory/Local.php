<?php

/**
 * @file
 * Contains \Drupal\flysystem\AdapterFactory\Local.
 */

namespace Drupal\flysystem\AdapterFactory;

use League\Flysystem\Adapter\Local;

class Local implements AdapterFactoryInterface {

  public static function canRegister() {
    return TRUE;
  }

  public static function create(array $config) {
    return new Local($config['root']);
  }

}
