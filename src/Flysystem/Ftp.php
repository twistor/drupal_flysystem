<?php

namespace Drupal\flysystem\Flysystem;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\flysystem\Flysystem\Adapter\MissingAdapter;
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

    if (empty($this->configuration['host'])) {
      $this->configuration['host'] = '127.0.0.1';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAdapter() {
    try {
      $adapter = new FtpAdapter($this->configuration);
      $adapter->connect();
    }

    catch (\RuntimeException $e) {
      // A problem connecting to the server.
      $adapter = new MissingAdapter();
    }

    return $adapter;
  }

  /**
   * {@inheritdoc}
   */
  public function ensure($force = FALSE) {
    if ($this->getAdapter() instanceof FtpAdapter) {
      return [[
        'severity' => RfcLogLevel::INFO,
        'message' => 'Successfully connected to %host:%port.',
        'context' => [
          '%host' => $this->configuration['host'],
          '%port' => isset($this->configuration['port']) ? $this->configuration['port'] : 21,
        ],
      ]];
    }

    return [[
      'severity' => RfcLogLevel::ERROR,
      'message' => 'There was an error connecting to the FTP server %host:%port.',
      'context' => [
        '%host' => $this->configuration['host'],
        '%port' => isset($this->configuration['port']) ? $this->configuration['port'] : 21,
      ],
    ]];
  }

}
