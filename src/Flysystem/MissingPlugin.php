<?php

/**
 * @file
 * Contains \Drupal\flysystem\Flysystem\MissingPlugin.
 */

namespace Drupal\flysystem\Flysystem;

use Drupal\flysystem\Flysystem\Adapter\MissingAdapter;
use Drupal\flysystem\Plugin\FlysystemPluginInterface;

/**
 * Drupal plugin for the "NullAdapter" Flysystem adapter.
 *
 * @Adapter(id = "missing")
 */
class MissingPlugin Implements FlysystemPluginInterface {

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
    // Should we do some kind of reporting here, since if this is used, that
    // means another plugin is missing?
    return [];
  }

}
