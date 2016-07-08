<?php

namespace Drupal\flysystem\Flysystem;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\flysystem\Flysystem\Adapter\MissingAdapter;
use Drupal\flysystem\Plugin\FlysystemPluginInterface;

/**
 * Drupal plugin for the "NullAdapter" Flysystem adapter.
 *
 * @Adapter(id = "missing")
 */
class Missing Implements FlysystemPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getAdapter() {
    return new MissingAdapter();
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl($uri) {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function ensure($force = FALSE) {
    return [[
      'severity' => RfcLogLevel::ERROR,
      'message' => 'The Flysystem driver is missing.',
      'context' => [],
    ]];
  }

}
