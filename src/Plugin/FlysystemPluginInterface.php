<?php

namespace Drupal\flysystem\Plugin;

interface FlysystemPluginInterface {

  /**
   * Returns the Flysystem adapter
   *
   * @return \League\Flysystem\AdapterInterface
   *   The Flsysytem adapter.
   */
  public function getAdapter();

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
  public function getExternalUrl($uri);

}
