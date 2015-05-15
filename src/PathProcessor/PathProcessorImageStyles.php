<?php

/**
 * @file
 * Contains \Drupal\flysystem\PathProcessor\PathProcessorImageStyles.
 */

namespace Drupal\flysystem\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a path processor to rewrite image styles URLs.
 *
 * As the route system does not allow arbitrary amount of parameters convert
 * the file path to a query parameter on the request.
 */
class PathProcessorImageStyles implements InboundPathProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    if (!preg_match('|^_flysystem/\w+/styles/|', $path, $matches)) {
      return $path;
    }

    $prefix = $matches[0];

    // Strip out path prefix.
    $rest = substr($path, strlen($prefix));

    if (substr_count($rest, '/') < 2) {
      return $path;
    }

    // Get the image style, scheme and path.
    list($image_style, $scheme, $file) = explode('/', $rest, 3);

    // Set the file as query parameter.
    $request->query->set('file', $file);

    return $prefix . $image_style . '/' . $scheme;
  }

}
