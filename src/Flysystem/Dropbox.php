<?php

/**
 * @file
 * Contains \Drupal\flysystem\Flysystem\Dropbox.
 */

namespace Drupal\flysystem\Flysystem;

use Dropbox\Client;
use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use League\Flysystem\Dropbox\DropboxAdapter;

/**
 * Drupal plugin for the "Dropbox" Flysystem adapter.
 *
 * @Adapter(id = "dropbox")
 */
class Dropbox implements FlysystemPluginInterface {

  use FlysystemUrlTrait;

  /**
   * The path prefix inside the Dropbox folder.
   *
   * @var string
   */
  protected $prefix;

  /**
   * The Dropbox API token.
   *
   * @var string
   */
  protected $token;

  /**
   * The Dropbox client ID.
   *
   * @var string
   */
  protected $clientId;

  /**
   * Constructs a Dropbox object.
   *
   * @param array $configuration
   *   Plugin configuration array.
   */
  public function __construct(array $configuration) {
    $this->prefix = isset($configuration['prefix']) ? $configuration['prefix'] : '';
    $this->token = $configuration['token'];
    $this->clientId = $configuration['client_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function getAdapter() {
    $client = new Client($this->token, $this->clientId);
    return new DropboxAdapter($client, $this->prefix);
  }

}
