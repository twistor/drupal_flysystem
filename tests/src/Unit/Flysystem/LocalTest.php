<?php

/**
 * @file
 * Contains \Drupal\Tests\flysystem\Unit\Flysystem\LocalTest.
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
 * Tests for \Drupal\flysystem\Flysystem\Local.
 *
 * @group Flysystem
 * @covers \Drupal\flysystem\Flysystem\Local
 */
class LocalTest extends \PHPUnit_Framework_TestCase {

  protected $one;
  protected $two;
  protected $urlGenerator;

  public function setUp() {
    $filesystem = new Filesystem(new LocalAdapter(__DIR__));
    $filesystem->deleteDir('flysystem');
    $filesystem->deleteDir('flysystem2');

    $this->one = __DIR__ . '/flysystem';
    $this->two = __DIR__ . '/flysystem2';

    mkdir($this->one);
    mkdir($this->two);

    $url_generator = $this->prophesize('Drupal\Core\Routing\UrlGenerator');
    $url_generator->generateFromRoute(Argument::cetera())->willReturn('download');
    $url_generator->generateFromPath(Argument::cetera())->willReturn('serve');
    $this->UrlGenerator = $url_generator->reveal();
  }

  public function tearDown() {
    $filesystem = new Filesystem(new LocalAdapter(__DIR__));
    $filesystem->deleteDir('flysystem');
    $filesystem->deleteDir('flysystem2');
  }

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

    // Can't autocreate dir because it's a file.
    $local = new Local(__DIR__, __FILE__);
    $this->assertInstanceOf('League\Flysystem\Adapter\NullAdapter', $local->getAdapter());
  }

  public function testGetExternalUrl() {
    // Public and root are different.
    $local = $this->getLocalPlugin($this->one, __DIR__, TRUE);
    $this->assertSame('download', $local->getExternalUrl('test://file.txt'));

    // Edge case.
    $local = $this->getLocalPlugin($this->one, $this->two, TRUE);
    $this->assertSame('download', $local->getExternalUrl('test://file.txt'));

    // Public is invalid.
    $local = $this->getLocalPlugin('asdfasdf', $this->two, TRUE);
    $this->assertSame('download', $local->getExternalUrl('test://file.txt'));

    // Public and root are the same.
    $local = $this->getLocalPlugin(__DIR__, __DIR__, TRUE);
    $this->assertSame('serve', $local->getExternalUrl('test://file.txt'));

    // Public and root are the same, but public is false.
    $local = $this->getLocalPlugin(__DIR__, __DIR__, FALSE);
    $this->assertSame('download', $local->getExternalUrl('test://file.txt'));

    // Root is inside public.
    $local = $this->getLocalPlugin(__DIR__, $this->one, TRUE);
    $this->assertSame('serve', $local->getExternalUrl('test://file.txt'));
  }

  public function testEnsure() {
    // Invalid root.
    $local = new Local($this->one, __FILE__);
    $this->assertSame(1, count($local->ensure()));
    $this->assertFileNotExists($this->one . '/.htaccess');

    // Valid, public root, won't write .htaccess.
    $local = new Local($this->one, $this->one, TRUE);
    $this->assertSame(0, count($local->ensure()));
    $this->assertFileNotExists($this->one . '/.htaccess');

    // Write .htaccess.
    $local = new Local($this->one, $this->one, FALSE);
    $this->assertSame(0, count($local->ensure()));
    $this->assertHtaccessFile($this->one . '/.htaccess');

    // File should still be there on subsequent calls.
    $this->assertSame(0, count($local->ensure()));
    $this->assertHtaccessFile($this->one . '/.htaccess');

    // Test fhat overwriting works.
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

  protected function assertHtaccessFile($file) {
    $this->assertFileExists($file);
    $this->assertSame(FileStorage::htaccessLines(), file_get_contents($file));
    $this->assertSame(0100444, stat($file)['mode']);
  }

  protected function getLocalPlugin($public_path, $root, $is_public = FALSE, $directory_permission = 0775) {
    $local = new Local($public_path, $root, $is_public, $directory_permission);
    $local->setUrlGenerator($this->UrlGenerator);

    return $local;
  }

}
