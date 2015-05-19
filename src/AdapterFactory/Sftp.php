<?php

/**
 * @file
 * Contains \Drupal\flysystem\AdapterFactory\Sftp.
 */

namespace Drupal\flysystem\AdapterFactory;

use League\Flysystem\Sftp\SftpAdapter;

class Sftp implements AdapterFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public static function canRegister() {
    return class_exists('League\Flysystem\Sftp\SftpAdapter');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(array $config) {
    return new SftpAdapter($config);
  }

}
