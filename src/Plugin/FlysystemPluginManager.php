<?php

namespace Drupal\flysystem\Plugin;

use Drupal\Component\Plugin\FallbackPluginManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages Flysystem plugins.
 */
class FlysystemPluginManager extends DefaultPluginManager implements FallbackPluginManagerInterface {

  /**
   * Constructs a Flysystem object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Flysystem', $namespaces, $module_handler, 'Drupal\flysystem\Plugin\FlysystemPluginInterface', 'Drupal\flysystem\Annotation\Adapter');
    $this->setCacheBackend($cache_backend, 'flysystem_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackPluginId($plugin_id, array $configuration = []) {
    return 'missing';
  }

  /**
   * {@inheritdoc}
   */
  protected function alterDefinitions(&$definitions) {
    // Remove definitions that are missing necessary extensions.
    foreach ($definitions as $id => $definition) {
      foreach ($definition['extensions'] as $extension) {
        if (extension_loaded($extension)) {
          continue;
        }

        unset($definitions[$id]);
        break;
      }
    }

    parent::alterDefinitions($definitions);
  }

}
