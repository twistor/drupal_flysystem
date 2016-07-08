<?php

namespace Drupal\flysystem\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a path processor to rewrite Flysystem URLs.
 *
 * As the route system does not allow arbitrary amount of parameters convert
 * the file path to a query parameter on the request.
 */
class FlysystemPathProcessor implements InboundPathProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    // Quick exit.
    if (strpos($path, '/_flysystem/') !== 0) {
      return $path;
    }

    // Stream wrapper protocols must conform to /^[a-zA-Z0-9+.-]+$/
    // Via php_stream_wrapper_scheme_validate() in the PHP source.
    if (!preg_match('|^/_flysystem/([a-zA-Z0-9+.-]+)/|', $path, $matches)) {
      return $path;
    }

    $rest = substr($path, strlen($matches[0]));

    // Support image styles.
    if (strpos($rest, 'styles/') === 0 && substr_count($rest, '/') >= 3) {
      list(, $image_style, $scheme, $file) = explode('/', $rest, 4);

      // Set the file as query parameter.
      $request->query->set('file', $file);

      return '/_flysystem/styles/' . $image_style . '/' . $scheme;
    }

    // Routes to FileDownloadController::download().
    $request->query->set('file', $rest);

    return '/_flysystem/' . $matches[1];
  }

}
