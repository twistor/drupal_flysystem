<?php

/**
 * @file
 * Contains \Drupal\flysystem\Flysystem\Null.
 */

namespace Drupal\flysystem\Flysystem;

use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use League\Flysystem\Adapter\NullAdapter;

/**
 * Drupal plugin for the "Null" Flysystem adapter.
 *
 * @Adapter(id = "null")
 */
class Null Implements FlysystemPluginInterface {

  use FlysystemUrlTrait;

  /**
   * {@inheritdoc}
   */
  public function getAdapter() {
    return new NullAdapter();
  }

  /**
   * {@inheritdoc}
   */
  public function ensure($force = FALSE) {
    return [];
  }

}
