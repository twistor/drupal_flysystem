<?php

/**
 * @file
 * Contains \Drupal\flysystem\Flysystem\S3v2.
 */

namespace Drupal\flysystem\Flysystem;

use Aws\S3\S3Client;
use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use League\Flysystem\AwsS3v2\AwsS3Adapter;

/**
 * Drupal plugin for the "S3v2" Flysystem adapter.
 *
 * @Adapter(id = "s3v2")
 */
class S3v2 implements FlysystemPluginInterface {

  use FlysystemUrlTrait;

  /**
   * Plugin configuration.
   *
   * @var array
   */
  protected $configuration;

  /**
   * The S3 bucket.
   *
   * @var string
   */
  protected $bucket;

  /**
   * The path prefix inside the bucket.
   *
   * @var string
   */
  protected $prefix;

  /**
   * Constructs a S3v2 object.
   *
   * @param array $configuration
   *   Plugin configuration array.
   */
  public function __construct(array $configuration) {
    $this->configuration = $configuration;
    $this->bucket = $configuration['bucket-name'];
    $this->prefix = isset($configuration['prefix']) ? $configuration['prefix'] : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getAdapter() {
    $client = S3Client::factory($this->configuration);
    return new AwsS3Adapter($client, $this->bucket, $this->prefix);
  }

}
