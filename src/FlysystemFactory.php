<?php

namespace Drupal\flysystem;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Site\Settings;
use Drupal\flysystem\Event\EnsureEvent;
use Drupal\flysystem\Event\FlysystemEvents;
use Drupal\flysystem\Flysystem\Adapter\CacheItemBackend;
use Drupal\flysystem\Flysystem\Adapter\DrupalCacheAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\Replicate\ReplicateAdapter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A factory for Flysystem filesystems.
 */
class FlysystemFactory {

  use SerializationStopperTrait;

  /**
   * Default settings.
   *
   * @var array
   */
  protected $defaults = [
    'driver' => '',
    'config' => [],
    'replicate' => FALSE,
    'cache' => FALSE,
    'name' => '',
    'description' => '',
  ];

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * A cache of filesystems.
   *
   * @var \League\Flysystem\FilesystemInterface[]
   */
  protected $filesystems = [];

  /**
   * The Flysystem plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * Created plugins.
   *
   * @var \Drupal\flysystem\Plugin\FlysystemPluginInterface[]
   */
  protected $plugins = [];

  /**
   * Settings for stream wrappers.
   *
   * @var array
   */
  protected $settings = [];

  /**
   * Constructs a FlysystemFactory object.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   The plugin manager.
   * @param \Drupal\Core\File\FileSystemInterface $filesystem
   *   The Drupal filesystem service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(PluginManagerInterface $plugin_manager, FileSystemInterface $filesystem, CacheBackendInterface $cache, EventDispatcherInterface $event_dispatcher) {
    $this->pluginManager = $plugin_manager;
    $this->cacheBackend = $cache;
    $this->eventDispatcher = $event_dispatcher;

    // Apply defaults and validate registered services.
    foreach (Settings::get('flysystem', []) as $scheme => $configuration) {

      // The settings.php file could be changed before rebuilding the container.
      if (!$filesystem->validScheme($scheme)) {
        continue;
      }

      $this->settings[$scheme] = $configuration + $this->defaults;
    }
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
  public function getFilesystem($scheme) {
    if (!isset($this->filesystems[$scheme])) {
      $this->filesystems[$scheme] = new Filesystem($this->getAdapter($scheme));
    }

    return $this->filesystems[$scheme];
  }

  /**
   * Returns the plugin for a scheme.
   *
   * @param string $scheme
   *   The scheme.
   *
   * @return \Drupal\flysystem\Plugin\FlysystemPluginInterface
   *   The plugin.
   */
  public function getPlugin($scheme) {
    if (!isset($this->plugins[$scheme])) {
      $settings = $this->getSettings($scheme);

      $this->plugins[$scheme] = $this->pluginManager->createInstance($settings['driver'], $settings['config']);
    }

    return $this->plugins[$scheme];
  }

  /**
   * Returns a list of valid schemes.
   *
   * @return string[]
   *   The list of valid schemes.
   */
  public function getSchemes() {
    return array_keys($this->settings);
  }

  /**
   * Finds the settings for a given scheme.
   *
   * @param string $scheme
   *   The scheme.
   *
   * @return array
   *   The settings array from settings.php.
   */
  public function getSettings($scheme) {
    return isset($this->settings[$scheme]) ? $this->settings[$scheme] : $this->defaults;
  }

  /**
   * Calls FlysystemPluginInterface::ensure() on each plugin.
   *
   * @param bool $force
   *   (optional) Wheter to force the insurance. Defaults to false.
   *
   * @return array
   *   Errors keyed by scheme.
   */
  public function ensure($force = FALSE) {
    $errors = [];

    foreach ($this->getSchemes() as $scheme) {

      foreach ($this->getPlugin($scheme)->ensure($force) as $error) {

        $event = new EnsureEvent(
          $scheme,
          $error['severity'],
          $error['message'],
          $error['context']
        );

        $this->eventDispatcher->dispatch(FlysystemEvents::ENSURE, $event);

        $errors[$scheme][] = $error;
      }
    }

    return $errors;
  }

  /**
   * Returns the adapter for a scheme.
   *
   * @param string $scheme
   *   The scheme to find an adapter for.
   *
   * @return \League\Flysystem\AdapterInterface
   *   The correct adapter from settings.
   */
  protected function getAdapter($scheme) {
    $settings = $this->getSettings($scheme);

    $adapter = $this->getPlugin($scheme)->getAdapter();

    if ($settings['replicate']) {
      $replica = $this->getAdapter($settings['replicate']);
      $adapter = new ReplicateAdapter($adapter, $replica);
    }

    if ($settings['cache']) {
      $cache_item_backend = new CacheItemBackend($scheme, $this->cacheBackend);
      $adapter = new DrupalCacheAdapter($scheme, $adapter, $cache_item_backend);
    }

    return $adapter;
  }

}
