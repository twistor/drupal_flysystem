<?php

namespace Drupal\Tests\flysystem\Unit;

use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\FlysystemBridge;
use Drupal\flysystem\FlysystemFactory;
use Drupal\flysystem\Flysystem\Adapter\MissingAdapter;
use Drupal\flysystem\Flysystem\Missing;
use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @coversDefaultClass \Drupal\flysystem\FlysystemBridge
 * @group flysystem
 */
class FlysystemBridgeTest extends UnitTestCase {

  /**
   * @var \Drupal\flysystem\FlysystemBridge
   */
  protected $bridge;

  /**
   * @var \League\Flysystem\FilesystemInterface
   */
  protected $filesystem;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->bridge = new FlysystemBridge();
    $this->bridge->setStringTranslation($this->getStringTranslationStub());
    $this->bridge->setUri('testscheme://file.txt');

    $factory = $this->prophesize(FlysystemFactory::class);
    $factory->getPlugin('testscheme')->willReturn(new Missing());

    $this->filesystem = new Filesystem(new MissingAdapter());

    $factory->getFilesystem('testscheme')->willReturn($this->filesystem);

    $factory->getSettings('testscheme')->willReturn(['name' => '', 'description' => '']);

    $container = new ContainerBuilder();
    $container->set('flysystem_factory', $factory->reveal());
    \Drupal::setContainer($container);
  }

  /**
   * @covers ::getType
   */
  public function testGetTypeReturnsWriteVisible() {
    $this->assertSame(StreamWrapperInterface::WRITE_VISIBLE, FlysystemBridge::getType());
  }

  /**
   * @covers ::getName
   */
  public function testGetNameFormattingCorrect() {
    $this->assertSame('Flysystem: testscheme', (string) $this->bridge->getName());
  }

  /**
   * @covers ::getDescription
   */
  public function testGetDescriptionFormattingCorrect() {
    $this->assertSame('Flysystem: testscheme', (string) $this->bridge->getDescription());
  }

  /**
   * @covers ::getUri
   * @covers ::setUri
   */
  public function testGetUriMatchesSetUri() {
    $this->bridge->setUri('beep://boop');
    $this->assertSame('beep://boop', $this->bridge->getUri());
  }

  /**
   * @covers ::getExternalUrl
   * @covers ::getFactory
   */
  public function testGetExternalUrlDelegatesToPlugin() {
    $this->assertSame('', $this->bridge->getExternalUrl('testscheme://testfile.txt'));
  }

  /**
   * @covers ::realpath
   */
  public function testRealpathIsFalse() {
    $this->assertFalse($this->bridge->realpath());
  }

  /**
   * @covers ::dirname
   */
  public function testDirname() {
    $this->assertSame('testscheme://', $this->bridge->dirname());
    $this->assertSame('testscheme://dir://dir', $this->bridge->dirname('testscheme:///dir://dir/file.txt'));
  }

  /**
   * @covers ::getFilesystem
   * @covers ::getFilesystemForScheme
   */
  public function testGetFilesystemOverridden() {
    $method = new \ReflectionMethod($this->bridge, 'getFilesystem');
    $method->setAccessible(TRUE);
    $this->assertSame($this->filesystem, $method->invoke($this->bridge));
  }

}
