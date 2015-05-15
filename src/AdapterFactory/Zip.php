<?php

/**
 * @file
 * Contains \Drupal\flysystem\AdapterFactory\Zip.
 */

namespace Drupal\flysystem\AdapterFactory;

use League\Flysystem\ZipArchive\ZipArchiveAdapter;

class Zip implements AdapterFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public static function canRegister() {
    return extension_loaded('zip');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(array $config) {
    $config += ['prefix' => ''];
    return new ZipArchiveAdapter($config['location'], NULL, $config['prefix']);
  }

}
