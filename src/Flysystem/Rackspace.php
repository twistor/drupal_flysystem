<?php

/**
 * @file
 * Contains \Drupal\flysystem\Flysystem\Rackspace.
 */

namespace Drupal\flysystem\Flysystem;

use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use League\Flysystem\Rackspace\RackspaceAdapter;
use OpenCloud\OpenStack;
use OpenCloud\Rackspace;

/**
 * Drupal plugin for the "Rackspace" Flysystem adapter.
 *
 * @Adapter(id = "rackspace")
 */
class Rackspace implements FlysystemPluginInterface {

  use FlysystemUrlTrait;

  /**
   * Plugin configuration.
   *
   * @var array
   */
  protected $configuration;

  /**
   * Constructs a Rackspace object.
   *
   * @param array $configuration
   *   Plugin configuration array.
   */
  public function __construct(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getAdapter() {
    $client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, $configuration);

    $store = $client->objectStoreService('cloudFiles', 'LON');
    $container = $store->getContainer('flysystem');

    return new RackspaceAdapter($container);
  }

}
