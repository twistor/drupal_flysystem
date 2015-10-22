<?php

/**
 * @file
 * Contains \Drupal\flysystem\Flysystem\Local.
 */

namespace Drupal\flysystem\Flysystem;

use Drupal\Component\PhpStorage\FileStorage;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\flysystem\Flysystem\Adapter\MissingAdapter;
use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use League\Flysystem\Adapter\Local as LocalAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drupal plugin for the "Local" Flysystem adapter.
 *
 * @Adapter(id = "local")
 */
class Local implements FlysystemPluginInterface, ContainerFactoryPluginInterface {

  use FlysystemUrlTrait {
    getExternalUrl as getDownloadlUrl;
  }

  /**
   * Whether the directory was recently created.
   *
   * @var bool
   */
  protected $created = FALSE;

  /**
   * The permissions to create directories with.
   *
   * @var int
   */
  protected $directoryPerm;

  /**
   * The root directory as it was supplied by the user.
   *
   * @var string
   */
  protected $originalRoot;

  /**
   * Whether the root is in the public path.
   *
   * @var string|false
   */
  protected $publicPath = FALSE;

  /**
   * The root of the local adapter.
   *
   * @var string
   */
  protected $root;

  /**
   * Constructs a Local object.
   *
   * @param string $public_filepath
   *   The path to the public files directory.
   * @param string $root
   *   The of the adapter's filesystem.
   * @param bool $is_public
   *   (optional) Whether this is a public file system. Defaults to false.
   * @param int $directory_permission
   *   (optional) The permissions to create directories with.
   */
  public function __construct($public_filepath, $root, $is_public = FALSE, $directory_permission = FileSystem::CHMOD_DIRECTORY) {
    $this->originalRoot = $root;
    $this->directoryPerm = $directory_permission;
    // ensureDirectory() sets the created flag.
    if (!$this->root = $this->ensureDirectory($root)) {
      return;
    }

    if ($is_public) {
      $this->publicPath = $this->pathIsPublic($public_filepath, $this->root);
    }

    // If the directory was recently created, write the .htaccess file.
    if ($this->created && $this->publicPath === FALSE) {
      $this->writeHtaccess($this->root);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $default = $container->get('kernel')->getSitePath() . '/files';
    $settings = $container->get('settings');

    return new static(
      $settings->get('file_public_path', $default),
      $configuration['root'],
      !empty($configuration['public']),
      $settings->get('file_chmod_directory', FileSystem::CHMOD_DIRECTORY)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getAdapter() {
    if ($this->root) {
      return new LocalAdapter($this->root);
    }

    return new MissingAdapter();
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl($uri) {
    if ($this->publicPath === FALSE) {
      return $this->getDownloadlUrl($uri);
    }

    $path = str_replace('\\', '/', $this->publicPath . '/' . $this->getTarget($uri));

    return $GLOBALS['base_url'] . '/' . UrlHelper::encodePath($path);
  }

  /**
   * {@inheritdoc}
   */
  public function ensure($force = FALSE) {
    if (!$this->root) {
      return [[
        'severity' => RfcLogLevel::ERROR,
        'message' => 'The %root directory either does not exist or is not readable and attempts to create it have failed.',
        'context' => ['%root' => $this->originalRoot],
      ]];
    }

    if ($this->publicPath !== FALSE || $this->writeHtaccess($this->root, $force)) {
      return [];
    }

    return [[
      'severity' => RfcLogLevel::ERROR,
      'message' => 'See <a href="@url">@url</a> for information about the recommended .htaccess file which should be added to the %directory directory to help protect against arbitrary code execution.',
      'context' => [
        '%directory' => $this->root,
        '@url' => 'https://www.drupal.org/SA-CORE-2013-003',
      ],
    ]];
  }

  /**
   * Checks that the directory exists and is readable.
   *
   * This will attempt to create the directory if it doesn't exist.
   *
   * @param string $directory
   *   The directory.
   *
   * @return string|false
   *   The path of the directory, or false on failure.
   */
  protected function ensureDirectory($directory) {
    // Go for the success case first.
    if (is_dir($directory) && is_readable($directory)) {
      return $directory;
    }

    if (!file_exists($directory)) {
      $success = mkdir($directory, $this->directoryPerm, TRUE);
    }

    if (is_dir($directory) && chmod($directory, $this->directoryPerm)) {
      clearstatcache(TRUE, $directory);
      $this->created = TRUE;
      return $directory;
    }

    return FALSE;
  }

  /**
   * Writes an .htaccess file.
   *
   * @param string $directory
   *   The directory to write the .htaccess file.
   * @param bool $force
   *   Whether to overwrite an existing file.
   *
   * @return bool
   *   True on success, false on failure.
   */
  protected function writeHtaccess($directory, $force = FALSE) {
    $htaccess_path = $directory . '/.htaccess';

    if (file_exists($htaccess_path) && !$force) {
      // Short circuit if the .htaccess file already exists.
      return TRUE;
    }

    // Write the .htaccess file.
    if (is_dir($directory) && is_writable($directory)) {
      return file_put_contents($htaccess_path, FileStorage::htaccessLines()) && chmod($htaccess_path, 0444);
    }

    return FALSE;
  }

  /**
   * Determines if the path is inside the public path.
   *
   * @param string $public_filepath
   *   The path to the public files directory.
   * @param string $root
   *   The root path.
   *
   * @return string|false
   *   The public path, or false.
   */
  protected function pathIsPublic($public_filepath, $root) {
    $root = realpath($root);

    if (!$public = realpath($public_filepath)) {
      return FALSE;
    }

    if (strpos($root . DIRECTORY_SEPARATOR, $public . DIRECTORY_SEPARATOR) === 0) {
      return $public_filepath . substr($root, strlen($public));
    }

    return FALSE;
  }

}
