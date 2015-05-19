<?php

/**
 * @file
 * Contains \Drupal\flysystem\AdapterFactory\Ftp.
 */

namespace Drupal\flysystem\AdapterFactory;

use League\Flysystem\Adapter\Ftp as FtpAdapter;

class Ftp implements AdapterFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public static function canRegister() {
    return extension_loaded('ftp');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(array $config) {
    return new FtpAdapter($config);
  }

}
