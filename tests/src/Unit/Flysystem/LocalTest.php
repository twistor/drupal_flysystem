<?php

/**
 * @file
 * Contains \NoDrupal\Tests\flysystem\Unit\Flysystem\LocalTest.
 */

namespace NoDrupal\Tests\flysystem\Unit\Flysystem;

use Drupal\Component\PhpStorage\FileStorage;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Site\Settings;
use Drupal\flysystem\Flysystem\Local;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Filesystem;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\flysystem\Flysystem\Local
 * @group flysystem
 */
class LocalTest extends \PHPUnit_Framework_TestCase {

  /**
   * The first directory path.
   *
   * @var string
   */
  protected $one;

  /**
   * The second test directory path.
   *
   * @var string
   */
  protected $two;

  /**
   * The test Url generator.
   *
   * @var \Drupal\Core\Routing\UrlGenerator
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->one = __DIR__ . '/flysystem';
    $this->two = __DIR__ . '/flysystem2';
    $this->cleanUpFiles();

    mkdir($this->one);
    mkdir($this->two);

    $url_generator = $this->prophesize('Drupal\Core\Routing\UrlGenerator');
    $url_generator->generateFromRoute(Argument::cetera())->willReturn('download');
    $this->UrlGenerator = $url_generator->reveal();
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $this->cleanUpFiles();
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  public function testCreate() {
    $kernel = $this->prophesize('Drupal\Core\DrupalKernelInterface');
    $kernel->getSitePath()->willReturn('');

    $container = new ContainerBuilder();
    $container->set('settings', new Settings([
      'file_public_path' => $this->one,
    ]));
    $container->set('kernel', $kernel->reveal());

    $configuration = ['root' => $this->two];

    $plugin = Local::create($container, $configuration, NULL, NULL);
    $this->assertInstanceOf('Drupal\flysystem\Plugin\FlysystemPluginInterface', $plugin);
  }

  /**
   * @covers ::getAdapter
   */
  public function testGetAdapter() {
    $local = new Local(__DIR__, $this->one);
    $this->assertInstanceOf('League\Flysystem\Adapter\Local', $local->getAdapter());

    // Test autocreate dir.
    $this->assertFalse(is_dir(__DIR__ . '/flysystem/sub'));
    $local = new Local($this->one, __DIR__ . '/flysystem/sub', FALSE, 0777);
    $this->assertInstanceOf('League\Flysystem\Adapter\Local', $local->getAdapter());
    $this->assertTrue(is_dir(__DIR__ . '/flysystem/sub'));
    $this->assertSame(040777, stat(__DIR__ . '/flysystem/sub')['mode']);
    $this->assertHtaccessFile(__DIR__ . '/flysystem/sub/.htaccess');

    // Test that directory perm is set if not readable.
    chmod(__DIR__ . '/flysystem/sub', 0111);
    $local = new Local($this->one, __DIR__ . '/flysystem/sub', FALSE, 0777);
    $this->assertSame(040777, stat(__DIR__ . '/flysystem/sub')['mode']);

    // Can't autocreate dir because it's a file.
    $local = new Local(__DIR__, __FILE__);
    $this->assertInstanceOf('Drupal\flysystem\Flysystem\Adapter\MissingAdapter', $local->getAdapter());
  }

  /**
   * @covers ::getExternalUrl
   * @covers ::pathIsPublic
   */
  public function testGetExternalUrl() {
    $GLOBALS['base_url'] = 'http://example.com';
    // Public and root are different.
    $local = $this->getLocalPlugin($this->one, __DIR__, TRUE);
    $this->assertSame('download', $local->getExternalUrl('test://file.txt'));
    $this->assertFileNotExists(__DIR__ . '/.htaccess');

    // Edge case.
    $local = $this->getLocalPlugin($this->one, $this->two, TRUE);
    $this->assertSame('download', $local->getExternalUrl('test://file.txt'));
    $this->assertFileNotExists($this->two . '/.htaccess');

    // Public is invalid.
    $local = $this->getLocalPlugin('asdfasdf', $this->two, TRUE);
    $this->assertSame('download', $local->getExternalUrl('test://file.txt'));

    // Public and root are the same.
    $local = $this->getLocalPlugin(__DIR__, __DIR__, TRUE);
    $this->assertSame('http://example.com/' . __DIR__ . '/file.txt', $local->getExternalUrl('test://file.txt'));
    $this->assertFileNotExists(__DIR__ . '/.htaccess');

    // Public and root are the same, but public is false.
    $local = $this->getLocalPlugin(__DIR__, __DIR__, FALSE);
    $this->assertSame('download', $local->getExternalUrl('test://file.txt'));
    $this->assertFileNotExists(__DIR__ . '/.htaccess');

    // Root is inside public.
    $local = $this->getLocalPlugin(__DIR__, $this->one, TRUE);
    $this->assertSame('http://example.com/' . $this->one . '/file.txt', $local->getExternalUrl('test://file.txt'));
    $this->assertFileNotExists($this->one . '/.htaccess');
  }

  /**
   * @covers ::ensure
   * @covers ::ensureDirectory
   * @covers ::writeHtaccess
   * @covers ::__construct
   *
   * @todo Clean this up.
   */
  public function testEnsure() {
    // Invalid root.
    $local = new Local($this->one, __FILE__);
    $this->assertSame(1, count($local->ensure()));
    $this->assertFileNotExists($this->one . '/.htaccess');

    // Valid, public root, won't write .htaccess.
    $local = new Local($this->one, $this->one, TRUE);
    $this->assertSame(0, count($local->ensure()));
    $this->assertFileNotExists($this->one . '/.htaccess');

    // Make sure mkdir is recursive.
    $local = new Local($this->one, $this->one . '/sub/deepersub', FALSE);
    $this->assertSame(0, count($local->ensure()));
    $this->assertHtaccessFile($this->one . '/sub/deepersub/.htaccess');

    // Write .htaccess.
    $local = new Local($this->one, $this->one, FALSE);
    $this->assertSame(0, count($local->ensure()));
    $this->assertHtaccessFile($this->one . '/.htaccess');

    // File should still be there on subsequent calls.
    $this->assertSame(0, count($local->ensure()));
    $this->assertHtaccessFile($this->one . '/.htaccess');

    // Test that overwriting works.
    chmod($this->one . '/.htaccess', 0777);
    file_put_contents($this->one . '/.htaccess', 'asjkhasdjsd');

    // Shouldn't overwrite.
    $this->assertSame(0, count($local->ensure()));
    $this->assertSame('asjkhasdjsd', file_get_contents($this->one . '/.htaccess'));

    // Should overwrite.
    $this->assertSame(0, count($local->ensure(TRUE)));
    $this->assertHtaccessFile($this->one . '/.htaccess');

    // Can't write .htaccess.
    unlink($this->one . '/.htaccess');
    chmod($this->one, 0444);
    $this->assertSame(1, count($local->ensure()));
    $this->assertFileNotExists($this->one . '/.htaccess');
    chmod($this->one, 0777);
  }

  /**
   * Asserts that the .htaccess file exists and has the correct contents.
   *
   * @param string $file
   *   The path to the .htaccess file.
   */
  protected function assertHtaccessFile($file) {
    $this->assertFileExists($file);
    $this->assertSame(FileStorage::htaccessLines(), file_get_contents($file));
    $this->assertSame(0100444, stat($file)['mode']);
  }

  /**
   * Removes test files.
   */
  protected function cleanUpFiles() {
    $filesystem = new Filesystem(new LocalAdapter(__DIR__));

    $dirs = [
      $this->one,
      $this->two,
      $this->one . '/sub',
      $this->one . '/sub/deepersub',
    ];

    foreach ($dirs as $dir) {
      if (is_dir($dir)) {
        chmod($dir, 0777);
      }
  }

    $filesystem->deleteDir('flysystem');
    $filesystem->deleteDir('flysystem2');
    if ($filesystem->has('.htaccess')) {
      $filesystem->delete('.htaccess');
    }
  }

  /**
   * Returns a new Local plugin instance.
   *
   * @param string $public_path
   *   The public path.
   * @param string $root
   *   The root path.
   * @param bool $is_public
   *   Whether this plugin should be public.
   * @param int $directory_permission
   *   The directory permission.
   *
   * @return \Drupal\flysystem\Flysystem\Local
   *   A new local plugin.
   */
  protected function getLocalPlugin($public_path, $root, $is_public = FALSE, $directory_permission = 0775) {
    $local = new Local($public_path, $root, $is_public, $directory_permission);
    $local->setUrlGenerator($this->UrlGenerator);

    return $local;
  }

}
