<?php

namespace Drupal\Tests\flysystem\Unit\Flysystem;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Site\Settings;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\Flysystem\Adapter\MissingAdapter;
use Drupal\flysystem\Flysystem\Local;
use League\Flysystem\Adapter\Local as LocalAdapter;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\flysystem\Flysystem\Local
 * @group flysystem
 */
class LocalTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $GLOBALS['base_url'] = 'http://example.com';

    $container = new ContainerBuilder();
    $url_generator = $this->prophesize(UrlGeneratorInterface::class);
    $url_generator->generateFromRoute(Argument::cetera())->willReturn('download');
    $container->set('url_generator', $url_generator->reveal());
    \Drupal::setContainer($container);
    (new LocalAdapter('foo/bar'))->deleteDir('');
    @rmdir('foo/bar');
    @rmdir('foo');
    mkdir('foo');
    mkdir('foo/bar');

    touch('test.txt');
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    (new LocalAdapter('foo'))->deleteDir('');
    @rmdir('foo');

    unlink('test.txt');

    @unlink('does_not_exist/.htaccess');
    @rmdir('does_not_exist');
  }

  /**
   * @covers ::__construct
   * @covers ::create
   */
  public function testCreateReturnsPlugin() {
    $container = new ContainerBuilder();
    $settings = new Settings([]);
    $container->set('settings', $settings);

    $configuration = ['root' => 'foo/bar'];

    $this->assertInstanceOf(Local::class, Local::create($container, $configuration, '', []));
  }

  /**
   * @covers ::getAdapter
   * @covers ::ensureDirectory
   */
  public function testReturnsLocalAdapter() {
    $this->assertInstanceOf(LocalAdapter::class, (new Local('foo/bar', FALSE))->getAdapter());
  }

  /**
   * @covers ::getAdapter
   * @covers ::ensureDirectory
   */
  public function testMissingAdapterReturnedWhenPathIsFile() {
    $this->assertInstanceOf(MissingAdapter::class, (new Local('test.txt'))->getAdapter());
  }

  /**
   * @covers ::getExternalUrl
   */
  public function testReturnsValidLocalUrl() {
    $plugin = new Local('foo/bar', FALSE);
    $this->assertSame('download', $plugin->getExternalUrl('uri://test.html'));
  }

  /**
   * @covers ::getExternalUrl
   */
  public function testReturnsValidExternalUrl() {
    $plugin = new Local('foo/bar', TRUE);
    $this->assertSame('http://example.com/foo/bar/test%20thing.html', $plugin->getExternalUrl('uri://test thing.html'));
  }

  /**
   * @covers ::ensure
   * @covers ::ensureDirectory
   */
  public function testDirectoryIsAutoCreatedAndHtaccessIsWritten() {
    $plugin = new Local('does_not_exist');
    $this->assertTrue(is_dir('does_not_exist'));
    $this->assertTrue(is_file('does_not_exist/.htaccess'));

  }

  /**
   * @covers ::ensure
   * @covers ::writeHtaccess
   */
  public function testHtaccessNotOverwritten() {
    file_put_contents('foo/bar/.htaccess', 'htcontent');

    $result = (new Local('foo/bar'))->ensure();

    $this->assertSame(1, count($result));
    $this->assertSame(RfcLogLevel::INFO, $result[0]['severity']);
    $this->assertSame('htcontent', file_get_contents('foo/bar/.htaccess'));
  }

  /**
   * @covers ::ensure
   * @covers ::writeHtaccess
   */
  public function testHtaccessNotOverwrittenAndFails() {
    mkdir('foo/bar/.htaccess', 0777, TRUE);

    $result = (new Local('foo/bar'))->ensure(TRUE);
    $this->assertSame(1, count($result));
    $this->assertSame('https://www.drupal.org/SA-CORE-2013-003', $result[0]['context']['@url']);
  }

  /**
   * @covers ::ensure
   * @covers ::writeHtaccess
   */
  public function testEnsureReturnsErrorWhenCantCreateDir() {
    $result = (new Local('test.txt'))->ensure();
    $this->assertSame('test.txt', $result[0]['context']['%root']);
  }

}
