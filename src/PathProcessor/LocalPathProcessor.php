<?php

namespace Drupal\flysystem\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a path processor to serve public files directly for the local
 * adapter.
 *
 * As the route system does not allow arbitrary amount of parameters convert
 * the file path to a query parameter on the request.
 */
class LocalPathProcessor implements InboundPathProcessorInterface {

  /**
   * The root of the local filesystem.
   *
   * @var string
   */
  protected $root;

  /**
   * The scheme.
   *
   * @var string
   */
  protected $scheme;

  /**
   * Constructs a LocalPathProcessor.
   *
   * @param string $scheme
   *   The public scheme.
   */
  public function __construct($scheme) {
    $this->scheme = $scheme;
    $settings = Settings::get('flysystem', []);
    $this->root = $settings[$scheme]['config']['root'];
  }

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    if (strpos($path, '/' . $this->root . '/') !== 0) {
      return $path;
    }

    $rest = substr($path, strlen($this->root) + 2);

    if (strpos($rest, 'styles/') === 0 && substr_count($rest, '/') >= 3) {
      list(, $image_style, $scheme, $file) = explode('/', $rest, 4);

      // Set the file as query parameter.
      $request->query->set('file', $file);

      return '/' . $this->root . '/styles/' . $image_style . '/' . $scheme;
    }

    $request->query->set('file', $rest);

    return '/' . $this->root;
  }

}
