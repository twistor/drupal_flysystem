<?php

/**
 * @file
 * Contains \NoDrupal\Tests\flysystem\Unit\FlysystemBridgeTest.
 */

namespace NoDrupal\Tests\flysystem\Unit;

use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\FlysystemBridge;
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
   * @covers \Drupal\flysystem\FlysystemBridge
   */
  public function testDrupalMethods() {
    $this->assertSame(StreamWrapperInterface::WRITE_VISIBLE, FlysystemBridge::getType());

    $bridge = new FlysystemBridge();
    $bridge->setStringTranslation($this->getStringTranslationStub());

    $uri = 'testscheme://file.txt';
    $bridge->setUri($uri);
    $this->assertSame('testscheme://file.txt', $bridge->getUri($uri));

    $this->assertSame('Flysystem: testscheme', (string) $bridge->getName());
    $this->assertSame('Flysystem: testscheme', (string) $bridge->getDescription());
    $this->assertFalse($bridge->realpath());
    $this->assertSame('testscheme://', $bridge->dirname());
    $this->assertSame('testscheme://dir://dir', $bridge->dirname('testscheme:///dir://dir/file.txt'));

    $factory = $this->prophesize('Drupal\flysystem\FlysystemFactory');
    $factory->getPlugin('testscheme')->willReturn(new Missing());
    $filesystem = new Filesystem(new MissingAdapter());
    $factory->getFilesystem('testscheme')->willReturn($filesystem);

    $container = new ContainerBuilder();
    \Drupal::setContainer($container);
    $container->set('flysystem_factory', $factory->reveal());

    $this->assertSame('', $bridge->getExternalUrl('testscheme://testfile.txt'));

    $method = new \ReflectionMethod($bridge, 'getFilesystem');
    $method->setAccessible(TRUE);
    $this->assertSame($filesystem, $method->invoke($bridge));
  }

}
