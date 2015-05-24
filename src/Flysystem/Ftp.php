<?php

/**
 * @file
 * Contains \Drupal\flysystem\Flysystem\Ftp.
 */

namespace Drupal\flysystem\Flysystem;

use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use League\Flysystem\Adapter\Ftp as FtpAdapter;

/**
 * Drupal plugin for the "FTP" Flysystem adapter.
 *
 * @Adapter(
 *   id = "ftp",
 *   extensions = {"ftp"}
 * )
 */
class Ftp implements FlysystemPluginInterface {

  use FlysystemUrlTrait;

  /**
   * Plugin configuration.
   *
   * @var array
   */
  protected $configuration;

  /**
   * Constructs an Ftp object.
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
    return new FtpAdapter($this->configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function ensure($force = FALSE) {
    // @todo Check that the connection is valid.
    return [];
  }

}
