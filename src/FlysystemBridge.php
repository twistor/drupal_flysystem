<?php

/**
 * @file
 * Contains \Drupal\flysystem\FlysystemBridge.
 */

namespace Drupal\flysystem;

use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use League\Flysystem\Util;
use Twistor\FlysystemStreamWrapper;

/**
 * An adapter for Flysystem to StreamWrapperInterface.
 */
class FlysystemBridge extends FlysystemStreamWrapper implements StreamWrapperInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getType() {
    return StreamWrapperInterface::WRITE_VISIBLE;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->t('Flysystem: @scheme', ['@scheme' => $this->getProtocol()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Flysystem: @scheme', ['@scheme' => $this->getProtocol()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getUri() {
    return $this->uri;
  }

  /**
   * {@inheritdoc}
   */
  public function setUri($uri) {
    $this->uri = $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl() {
    return $this
      ->getFactory()
      ->getPlugin($this->getProtocol())
      ->getExternalUrl($this->uri);
  }

  /**
   * {@inheritdoc}
   */
  public function realpath() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function dirname($uri = NULL) {
    if (!isset($uri)) {
      $uri = $this->uri;
    }

    list($scheme, $target) = explode('://', $uri, 2);

    return $scheme . '://' . ltrim(Util::dirname($target), '\/');
  }

  /**
   * Returns the filesystem for a given scheme.
   *
   * @param string $scheme
   *   The scheme.
   *
   * @return \League\Flysystem\FilesystemInterface
   *   The filesystem for the scheme.
   */
  protected function getFilesystemForScheme($scheme) {
    if (!isset(static::$filesystems[$scheme])) {
      static::$filesystems[$scheme] = $this->getFactory()->getFilesystem($scheme);
      static::$config[$scheme] = static::$defaultConfiguration;
      static::$config[$scheme]['permissions']['dir']['public'] = 0777;
      static::registerPlugins($scheme, static::$filesystems[$scheme]);
    }

    return static::$filesystems[$scheme];
  }

  /**
   * {@inheritdoc}
   */
  protected function getFilesystem() {
    if (!isset($this->filesystem)) {
      $this->filesystem = $this->getFilesystemForScheme($this->getProtocol());
    }

    return $this->filesystem;
  }

  /**
   * Returns the filesystem factory.
   *
   * @return \Drupal\flysystem\FlysystemFactory
   *   The Flysystem factory.
   */
  protected function getFactory() {
    return \Drupal::service('flysystem_factory');
  }

}
