<?php

/**
 * @file
 * Contains \Drupal\flysystem\AdapterFactory\Rackspace.
 */

namespace Drupal\flysystem\AdapterFactory;

use League\Flysystem\Rackspace\RackspaceAdapter;
use OpenCloud\OpenStack;
use OpenCloud\Rackspace;

class Rackspace implements AdapterFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public static function canRegister() {
    return class_exists('League\Flysystem\Rackspace\RackspaceAdapter');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(array $config) {
    $client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, $config);

    $store = $client->objectStoreService('cloudFiles', 'LON');
    $container = $store->getContainer('flysystem');

    return new RackspaceAdapter($container);
  }

}
