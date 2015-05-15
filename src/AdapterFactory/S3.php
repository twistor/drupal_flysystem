<?php

/**
 * @file
 * Contains \Drupal\flysystem\AdapterFactory\S3.
 */

namespace Drupal\flysystem\AdapterFactory;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v2\AwsS3Adapter;

class S3 implements AdapterFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public static function canRegister() {
    return class_exists('League\Flysystem\AwsS3v2\AwsS3Adapter');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(array $config) {
    $config += ['prefix' => ''];
    $client = S3Client::factory($config['s3']);

    return new AwsS3Adapter($client, $config['bucket-name'], $config['prefix']);
  }

}
