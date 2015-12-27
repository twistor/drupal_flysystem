<?php

/**
 * Contains \Drupal\flysystem\Asset\JsCollectionOptimizer.
 */

namespace Drupal\flysystem\Asset;

use Drupal\Core\Asset\JsCollectionOptimizer as DrupalJsCollectionOptimizer;

/**
 * Optimizes JavaScript assets.
 */
class JsCollectionOptimizer extends DrupalJsCollectionOptimizer {

  use SchemeExtensionTrait;

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    $this->state->delete('system.js_cache_files');
    $delete_stale = function($uri) {
      // Default stale file threshold is 30 days.
      if (REQUEST_TIME - filemtime($uri) > \Drupal::config('system.performance')->get('stale_file_threshold')) {
        file_unmanaged_delete($uri);
      }
    };
    file_scan_directory($this->getSchemeForExtension('js') . '://js', '/.*/', ['callback' => $delete_stale]);
  }

}
