<?php

/**
 * @file
 * Contains \Drupal\flysystem\Controller\DownloadController.
 */

namespace Drupal\flysystem\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Site\Settings;
use Drupal\flysystem\FlysystemFactory;
use Drupal\flysystem\SerializationStopperTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Allows Flysystem schemes to be downloaded.
 */
class DownloadController extends ControllerBase {

  use SerializationStopperTrait;

  /**
   * The Flysytem factory.
   *
   * @var \Drupal\flysystem\FlysystemFactory
   */
  protected $factory;

  /**
   * The mime type guesser.
   *
   * @var \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface
   */
  protected $guesser;

  /**
   * The Flysystem settings.
   *
   * @var array
   */
  protected $settings;

  /**
   * Constructs a DownloadController object.
   *
   * @param \Drupal\flysystem\FlysystemFactory $factory
   *   The Flysystem factory.
   * @param Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface $guesser
   *   The mimetype guesser.
   * @param array $settings
   *   The Flysystem settings.
   */
  public function __construct(FlysystemFactory $factory, MimeTypeGuesserInterface $guesser, array $settings) {
    $this->factory = $factory;
    $this->guesser = $guesser;
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('flysystem_factory'),
      $container->get('file.mime_type.guesser.extension'),
      $container->get('settings')->get('flysystem', [])
    );
  }

  /**
   * Serves a file.
   *
   * @param string $scheme
   *   The scheme.
   * @param string $path
   *   The file path.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
   *   A file response.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Thrown if the file does not exist.
   */
  public function serve($scheme, $path) {
    if (!isset($this->settings[$scheme])) {
      throw new NotFoundHttpException();
    }

    $filesystem = $this->factory->getFilesystem($scheme);

    if (!$filesystem->has($path)) {
      throw new NotFoundHttpException();
    }

    $headers = [
      'Content-Type' => $this->guesser->guess($path),
      'Content-Length' => $filesystem->getSize($path),
    ];

    return new BinaryFileResponse($scheme . '://' . $path, 200, $headers);
  }

}
