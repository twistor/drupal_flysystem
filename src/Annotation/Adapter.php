<?php

namespace Drupal\flysystem\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Flysystem adapter plguin.
 *
 * Plugin Namespace: Flysystem
 *
 * For a working example, see \Drupal\flysystem\Flysystem\Local.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class Adapter extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * A list of extension dependencies.
   *
   * @var string[]
   */
  public $extensions = [];

}
