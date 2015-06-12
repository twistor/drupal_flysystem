<?php

/**
 * @file
 * Contains \Drupal\Tests\flysystem\Unit\InstallFunctionsTest.
 */

namespace Drupal\Tests\flysystem\Unit;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests flysystem.install functions.
 *
 * @group flysystem
 */
class InstallFunctionsTest extends UnitTestCase {

  protected $factory;

  public function setUp() {
    parent::setUp();
    require_once dirname(dirname(dirname(__DIR__))) . '/flysystem.install';

    $this->factory = $this->prophesize('Drupal\flysystem\FlysystemFactory');

    $container = new ContainerBuilder();
    $container->set('flysystem_factory', $this->factory->reveal());
    $container->set('string_translation', $this->getStringTranslationStub());

    \Drupal::setContainer($container);
  }

  public function testFlysystemRequirements() {
    if (!defined('REQUIREMENT_ERROR')) {
      define('REQUIREMENT_ERROR', 2);
    }

    $return = flysystem_requirements('install');
    $this->assertSame(0, count($return));

    $this->factory->ensure()->willReturn([
      'testscheme' => [[
        'message' => 'Test message',
        'context' => [],
      ]],
    ]);

    $return = flysystem_requirements('runtime');

    $this->assertSame(1, count($return));
    $this->assertSame('Test message', $return['flysystem:testscheme']['description']);
  }

  public function testFlysystemInstall() {
    $this->factory->ensure()->shouldBeCalled();
    flysystem_install();
  }

}
