<?php

/**
 * @file
 * Contains \Drupal\flysystem\AdapterFactory\Dropbox.
 */

namespace Drupal\flysystem\AdapterFactory;

use League\Flysystem\Dropbox\DropboxAdapter;
use Dropbox\Client;

class Dropbox implements AdapterFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public static function canRegister() {
    return class_exists('League\Flysystem\Dropbox\DropboxAdapter');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(array $config) {
    $config += ['prefix' => ''];

    $client = new Client($config['token'], $config['client_id']);

    return new DropboxAdapter($client, $config['prefix']);
  }

}
