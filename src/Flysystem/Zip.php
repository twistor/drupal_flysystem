<?php

/**
 * @file
 * Contains \Drupal\flysystem\Flysystem\Zip.
 */

namespace Drupal\flysystem\Flysystem;

use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

/**
 * Drupal plugin for the "Zip" Flysystem adapter.
 *
 * @Adapter(
 *   id = "zip",
 *   extensions = {"zip"}
 * )
 */
class Zip implements FlysystemPluginInterface {

  use FlysystemUrlTrait;

  /**
   * The location of the zip file.
   *
   * @var string
   */
  protected $location;

  /**
   * The internal prefix.
   *
   * @var string
   */
  protected $prefix;

  /**
   * Constructs a Zip object.
   *
   * @param array $configuration
   *   Plugin configuration array.
   */
  public function __construct(array $configuration) {
    $this->location = $configuration['location'];
    $this->prefix = isset($configuration['prefix']) ? $configuration['prefix'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getAdapter() {
    return new ZipArchiveAdapter($this->location, NULL, $this->prefix);
  }

}
