<?php

namespace Drupal\Tests\flysystem\Unit\Plugin;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Lock\NullLockBackend;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\Plugin\ImageStyleGenerationTrait;
use Drupal\image\Entity\ImageStyle;
use Prophecy\Argument;
use org\bovigo\vfs\vfsStream;

/**
 * @coversDefaultClass \Drupal\flysystem\Plugin\ImageStyleGenerationTrait
 * @group flysystem
 */
class ImageStyleGenerationTraitTest extends UnitTestCase {

  /**
   * @covers ::generateImageStyle
   */
  public function testGenerateImageStyle() {
    vfsStream::setup('flysystem');
    touch('vfs://flysystem/foo.jpg');
    mkdir('vfs://flysystem/styles/pass', 0777, TRUE);

    $container = new ContainerBuilder();

    $image_style = $this->prophesize(ImageStyle::class);
    $image_style->buildUri('vfs://flysystem/foo.jpg')->willReturn('vfs://flysystem/styles/pass/foo.jpg');
    $image_style->buildUri('vfs://flysystem/foo.jpg.png')->willReturn('vfs://flysystem/styles/pass/foo.jpg.png');
    $image_style->id()->willReturn('pass');
    $image_style->createDerivative('vfs://flysystem/foo.jpg', 'vfs://flysystem/styles/pass/foo.jpg')->willReturn(TRUE);
    $image_style->createDerivative('vfs://flysystem/foo.jpg', 'vfs://flysystem/styles/pass/foo.jpg.png')->willReturn(TRUE);

    $storage = $this->prophesize(EntityStorageInterface::class);
    $storage->load('pass')->willReturn($image_style->reveal());
    $storage->load('fail')->willReturn(FALSE);

    $entity_manager = $this->prophesize(EntityManagerInterface::class);
    $entity_manager->getEntityTypeFromClass(ImageStyle::class)->willReturn('image_style');
    $entity_manager->getStorage('image_style')->willReturn($storage->reveal());

    $container->set('entity.manager', $entity_manager->reveal());
    $container->set('lock', new NullLockBackend());

    \Drupal::setContainer($container);

    $trait = $this->getMockForTrait(ImageStyleGenerationTrait::class);

    $method = (new \ReflectionMethod($trait, 'generateImageStyle'))->getClosure($trait);

    // Test invlid paths.
    $this->assertFalse($method('foo/bar'));
    $this->assertFalse($method('styles/image_style/vfs'));

    // Test invalid image style.
    $this->assertFalse($method('styles/fail/vfs/flysystem/foo.jpg'));

    // Test existing derivative.
    touch('vfs://flysystem/styles/pass/foo.jpg');
    $this->assertTrue($method('styles/pass/vfs/flysystem/foo.jpg'));
    unlink('vfs://flysystem/styles/pass/foo.jpg');

    // Basic passing.
    $this->assertTrue($method('styles/pass/vfs/flysystem/foo.jpg'));
    $this->assertTrue($method('styles/pass/vfs/flysystem/foo.jpg.png'));

    // Test failed lock.
    $fail_lock = $this->prophesize(LockBackendInterface::class);
    $fail_lock->acquire(Argument::type('string'))->willReturn(FALSE);
    $container->set('lock', $fail_lock->reveal());
    $this->assertFalse($method('styles/pass/vfs/flysystem/foo.jpg'));
    $container->set('lock', new NullLockBackend());

    // Test missing source.
    unlink('vfs://flysystem/foo.jpg');
    $this->assertFalse($method('styles/pass/vfs/flysystem/foo.jpg.png'));
  }

}
