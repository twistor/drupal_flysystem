<?php

namespace Drupal\flysystem\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class DownloadController extends ControllerBase {

  public function serve($scheme, $path, Request $request) {
    $uri = $scheme . '://' . $path;

    if (!file_stream_wrapper_valid_scheme($scheme) || !file_exists($uri)) {
      throw new NotFoundHttpException();
    }

    // Let other modules provide headers and controls access to the file.
    $headers = $this->moduleHandler()->invokeAll('file_download', array($uri));

    if (empty($headers) || in_array(-1, $headers)) {
      throw new AccessDeniedHttpException();
    }

    return new BinaryFileResponse($uri, 200, $headers);
  }

}
