<?php

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
   * The permissions to create directories with.
   *
   * @var int
   */
  protected $directoryPerm;

  /**
   * Whether the root is in the public path.
   *
   * @var bool
   */
  protected $isPublic;

  /**
   * The root of the local adapter.
   *
   * @var string
   */
  protected $root;

  /**
   * Whether the root exists and is readable.
   *
   * @var bool
   */
  protected $rootExists;

  /**
   * Constructs a Local object.
   *
   * @param string $root
   *   The of the adapter's filesystem.
   * @param bool $is_public
   *   (optional) Whether this is a public file system. Defaults to false.
   * @param int $directory_permission
   *   (optional) The permissions to create directories with.
   */
  public function __construct($root, $is_public = FALSE, $directory_permission = FileSystem::CHMOD_DIRECTORY) {
    $this->isPublic = $is_public;
    $this->root = $root;
    $this->directoryPerm = $directory_permission;
    $this->rootExists = $this->ensureDirectory();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration['root'],
      !empty($configuration['public']),
      $container->get('settings')->get('file_chmod_directory', FileSystem::CHMOD_DIRECTORY)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getAdapter() {
    return $this->rootExists ? new LocalAdapter($this->root) : new MissingAdapter();
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl($uri) {
    if ($this->isPublic === FALSE) {
      return $this->getDownloadlUrl($uri);
    }

    $path = str_replace('\\', '/', $this->root . '/' . $this->getTarget($uri));

    return $GLOBALS['base_url'] . '/' . UrlHelper::encodePath($path);
  }

  /**
   * {@inheritdoc}
   */
  public function ensure($force = FALSE) {
    if (!$this->rootExists) {
      return [[
        'severity' => RfcLogLevel::ERROR,
        'message' => 'The %root directory either does not exist or is not readable and attempts to create it have failed.',
        'context' => ['%root' => $this->root],
      ]];
    }

    if (!$this->writeHtaccess($force)) {
      return [[
        'severity' => RfcLogLevel::ERROR,
        'message' => 'See <a href="@url">@url</a> for information about the recommended .htaccess file which should be added to the %directory directory to help protect against arbitrary code execution.',
        'context' => [
          '%directory' => $this->root,
          '@url' => 'https://www.drupal.org/SA-CORE-2013-003',
        ],
      ]];
    }

    return [[
      'severity' => RfcLogLevel::INFO,
      'message' => 'The directory %root exists and is readable.',
      'context' => ['%root' => $this->root],
    ]];
  }

  /**
   * Checks that the directory exists and is readable.
   *
   * This will attempt to create the directory if it doesn't exist.
   *
   * @return bool
   *   True on success, false on failure.
   */
  protected function ensureDirectory() {
    // Go for the success case first.
    if (is_dir($this->root) && is_readable($this->root)) {
      return TRUE;
    }

    if (!file_exists($this->root)) {
      mkdir($this->root, $this->directoryPerm, TRUE);
    }

    if (is_dir($this->root) && chmod($this->root, $this->directoryPerm)) {
      clearstatcache(TRUE, $this->root);
      $this->writeHtaccess(TRUE);
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Writes an .htaccess file.
   *
   * @param bool $force
   *   Whether to overwrite an existing file.
   *
   * @return bool
   *   True on success, false on failure.
   */
  protected function writeHtaccess($force) {
    $htaccess_path = $this->root . '/.htaccess';

    if (file_exists($htaccess_path) && !$force) {
      // Short circuit if the .htaccess file already exists.
      return TRUE;
    }

    // Make file writable so that we can overwrite it.
    if (file_exists($htaccess_path)) {
      chmod($htaccess_path, 0666);
    }

    return @file_put_contents($htaccess_path, FileStorage::htaccessLines(!$this->isPublic)) && chmod($htaccess_path, 0444);
  }

}
