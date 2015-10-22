<?php

/**
 * @file
 * Contains \Drupal\Tests\flysystem\Unit\InstallFunctionsTest.
 */

namespace Drupal\Tests\flysystem\Unit;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests flysystem.install functions.
 *
 * @group flysystem
 */
class InstallFunctionsTest extends UnitTestCase {

  /**
   * The Flysystem factory prophecy object.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $factory;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    require_once dirname(dirname(dirname(__DIR__))) . '/flysystem.install';

    $this->factory = $this->prophesize('Drupal\flysystem\FlysystemFactory');

    $container = new ContainerBuilder();
    $container->set('flysystem_factory', $this->factory->reveal());
    $container->set('string_translation', $this->getStringTranslationStub());

    \Drupal::setContainer($container);
  }

  /**
   * Tests flysystem_requirements().
   */
  public function testFlysystemRequirements() {
    if (!defined('REQUIREMENT_ERROR')) {
      define('REQUIREMENT_ERROR', 2);
    }

    $dependencies_exist = (int) class_exists('League\Flysystem\Filesystem');

    $return = flysystem_requirements('update');
    $this->assertSame(0, count($return));

    $return = flysystem_requirements('install');
    $this->assertSame(1 - $dependencies_exist, count($return));

    $this->factory->ensure()->willReturn([
      'testscheme' => [[
        'message' => 'Test message',
        'context' => [],
        'severity' => RfcLogLevel::ERROR,
      ]],
    ]);

    $return = flysystem_requirements('runtime');

    $this->assertSame(2 - $dependencies_exist, count($return));
    $this->assertSame('Test message', (string) $return['flysystem:testscheme']['description']);
  }

  /**
   * Tests flysystem_install().
   */
  public function testFlysystemInstall() {
    $this->factory->ensure()->shouldBeCalled();
    flysystem_install();
  }

}
