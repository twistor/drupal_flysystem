<?php

/**
 * @file
 * Contains \Drupal\flysystem\Flysystem\Sftp.
 */

namespace Drupal\flysystem\Flysystem;

use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use League\Flysystem\Sftp\SftpAdapter;

/**
 * Drupal plugin for the "SFTP" Flysystem adapter.
 *
 * @Adapter(id = "sftp")
 */
class Sftp implements FlysystemPluginInterface {

  use FlysystemUrlTrait;

  /**
   * Plugin configuration.
   *
   * @var array
   */
  protected $configuration;

  /**
   * Constructs an Sftp object.
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
    return new SftpAdapter($this->configuration);
  }

}
