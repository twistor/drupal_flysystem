<?php

namespace Drupal\flysystem\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Site\Settings;
use Drupal\flysystem\FlysystemBridge;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class DownloadController extends ControllerBase {

  public function serve($scheme, $path) {
    if (!isset(Settings::get('flysystem', [])[$scheme])) {
      throw new NotFoundHttpException();
    }

    $filesystem = FlysystemBridge::getFilesystemForScheme($scheme);

    if (!$filesystem->has($path)) {
      throw new NotFoundHttpException();
    }

    $headers = array(
      'Content-Type' => \Drupal::service('file.mime_type.guesser.extension')->guess($path),
      'Content-Length' => $filesystem->getSize($path),
    );

    return new BinaryFileResponse($scheme . '://' . $path, 200, $headers);
  }

}
