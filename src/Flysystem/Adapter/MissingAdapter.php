<?php

namespace Drupal\flysystem\Flysystem\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;

/**
 * An adapter used when a plugin is missing. It fails at everything.
 */
class MissingAdapter implements AdapterInterface {

  /**
   * {@inheritdoc}
   */
  public function copy($path, $newpath) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function createDir($dirname, Config $config) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function delete($path) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteDir($dirname) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function listContents($directory = '', $recursive = FALSE) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata($path) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getMimetype($path) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSize($path) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getTimestamp($path) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getVisibility($path) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function has($path) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setVisibility($path, $visibility) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function update($path, $contents, Config $config) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function updateStream($path, $resource, Config $config) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function read($path) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function readStream($path) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function rename($path, $newpath) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function write($path, $contents, Config $config) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function writeStream($path, $resource, Config $config) {
    return FALSE;
  }

}
