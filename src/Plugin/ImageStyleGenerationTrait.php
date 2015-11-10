<?php

/**
 * @file
 * Contains \Drupal\flysystem\Plugin\ImageStyleGenerationTrait.
 */

namespace Drupal\flysystem\Plugin;

use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;

/**
 * Helper trait for generating URLs from adapter plugins.
 */
trait ImageStyleGenerationTrait {

  /**
   * Generates an image style for a file target.
   *
   * @param string $target
   *   The file target.
   *
   * @return bool
   *   True on success, false on failure.
   */
  protected function generateImageStyle($target) {
    if (strpos($target, 'styles/') !== 0 || substr_count($target, '/') < 3) {
      return FALSE;
    }

    list(, $style, $scheme, $file) = explode('/', $target, 4);

    if (!$image_style = ImageStyle::load($style)) {
      return FALSE;
    }

    $token = $image_style->getPathToken($scheme . '://' . $file);

    $parameters = ['scheme' => $scheme, 'filepath' => $target];
    $options = ['query' => ['itok' => $token], 'absolute' => TRUE];
    $url = Url::fromRoute('flysystem.serve', $parameters, $options);

    // @todo This should probably be a sub-request, but at the moment, that is
    // causing weird errors.
    try {
      $response = \Drupal::httpClient()->get($url->toString());
    }
    catch (\Exception $e) {
      return FALSE;
    }

    return $response->getStatusCode() == 200;
  }

}
