<?php

namespace Drupal\flysystem\Plugin;

use Drupal\Core\Url;
use League\Flysystem\Util;

/**
 * Helper trait for generating URLs from adapter plugins.
 */
trait FlysystemUrlTrait {

  /**
   * Returns a web accessible URL for the resource.
   *
   * This function should return a URL that can be embedded in a web page
   * and accessed from a browser. For example, the external URL of
   * "youtube://xIpLd0WQKCY" might be
   * "http://www.youtube.com/watch?v=xIpLd0WQKCY".
   *
   * @param string $uri
   *   The URI to provide a URL for.
   *
   * @return string
   *   Returns a string containing a web accessible URL for the resource.
   */
  public function getExternalUrl($uri) {
    $path = str_replace('\\', '/', $this->getTarget($uri));

    $arguments = [
      'scheme' => $this->getScheme($uri),
      'filepath' => $path,
    ];

    return Url::fromRoute('flysystem.serve', $arguments, ['absolute' => TRUE])->toString();
  }

  /**
   * Returns the target file path of a URI.
   *
   * @param string $uri
   *   The URI.
   *
   * @return string
   *   The file path of the URI.
   */
  protected function getTarget($uri) {
    return Util::normalizePath(substr($uri, strpos($uri, '://') + 3));
  }

  /**
   * Returns the scheme from the internal URI.
   *
   * @param string $uri
   *   The URI.
   *
   * @return string
   *   The scheme.
   */
  protected function getScheme($uri) {
    return substr($uri, 0, strpos($uri, '://'));
  }

}
