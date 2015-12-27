<?php

/**
 * Contains \Drupal\flysystem\Asset\CssCollectionOptimizer.
 */

namespace Drupal\flysystem\Asset;

use Drupal\Core\Asset\CssCollectionOptimizer as DrupalCssCollectionOptimizer;

/**
 * Optimizes CSS assets.
 */
class CssCollectionOptimizer extends DrupalCssCollectionOptimizer {

  use SchemeExtensionTrait;

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    $this->state->delete('drupal_css_cache_files');

    $delete_stale = function($uri) {
      // Default stale file threshold is 30 days.
      if (REQUEST_TIME - filemtime($uri) > \Drupal::config('system.performance')->get('stale_file_threshold')) {
        file_unmanaged_delete($uri);
      }
    };
    file_scan_directory($this->getSchemeForExtension('css') . '://css', '/.*/', ['callback' => $delete_stale]);
  }

}
