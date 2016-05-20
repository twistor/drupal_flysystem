<?php

namespace Drupal\flysystem\Asset;

use Drupal\Core\Asset\CssOptimizer as DrupalCssOptimizer;

/**
 * Changes Drupal\Core\Asset\CssOptimizer to not remove absolute URLs.
 *
 * @codeCoverageIgnore
 */
class CssOptimizer extends DrupalCssOptimizer {

  /**
   * {@inheritdoc}
   */
  public function rewriteFileURI($matches) {
    // Prefix with base and remove '../' segments where possible.
    $path = $this->rewriteFileURIBasePath . $matches[1];
    $last = '';
    while ($path != $last) {
      $last = $path;
      $path = preg_replace('`(^|/)(?!\.\./)([^/]+)/\.\./`', '$1', $path);
    }

    // file_url_transform_relative() was removed here.
    return 'url(' . file_create_url($path) . ')';
  }

}
