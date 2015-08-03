<?php

/**
 * @file
 * Contains \Drupal\flysystem\Flysystem\Adapter\ReplicateAdapter.
 */

namespace Drupal\flysystem\Flysystem\Adapter;

use League\Flysystem\Config;
use League\Flysystem\Replicate\ReplicateAdapter as FlysystemReplicateAdapter;
use League\Flysystem\Util;

/**
 * @todo Remove after
 *   https://github.com/thephpleague/flysystem-replicate-adapter/pull/5
 *
 * @codeCoverageIgnore
 */
class ReplicateAdapter extends FlysystemReplicateAdapter {

  /**
   * {@inheritdoc}
   */
  public function updateStream($path, $resource, Config $config) {
    if (!$this->source->updateStream($path, $resource, $config)) {
      return FALSE;
    }

    if (!$resource = $this->ensureSeekable($resource, $path)) {
      return FALSE;
    }

    if ($this->replica->has($path)) {
      return $this->replica->updateStream($path, $resource, $config);
    }

    return $this->replica->writeStream($path, $resource, $config);
  }

  /**
   * {@inheritdoc}
   */
  public function writeStream($path, $resource, Config $config) {
    if (!$this->source->writeStream($path, $resource, $config)) {
      return FALSE;
    }

    if (!$resource = $this->ensureSeekable($resource, $path)) {
      return FALSE;
    }

    return $this->replica->writeStream($path, $resource, $config);
  }

  /**
   * Rewinds the stream, or returns the source stream if not seekable.
   *
   * @param resource $resource
   *   The resource to rewind.
   * @param string $path
   *   The path where the resource exists.
   *
   * @return resource
   *   A stream set to position zero.
   */
  protected function ensureSeekable($resource, $path) {
    if (Util::isSeekableStream($resource) && rewind($resource)) {
      return $resource;
    }

    $stream = $this->source->readStream($path);

    return $stream ? $stream['stream'] : FALSE;
  }

}
