<?php

namespace Drupal\flysystem\Plugin;

use Drupal\Core\Routing\UrlGeneratorTrait;

trait FlysystemUrlTrait {

  use UrlGeneratorTrait;

  public function getExternalUrl($uri) {
    list($scheme, $path) = explode('://', $uri, 2);
    $path = str_replace('\\', '/', $path);

    return $this->url('flysystem.download', ['scheme' => $scheme, 'path' => $path], ['absolute' => TRUE]);
  }

}
