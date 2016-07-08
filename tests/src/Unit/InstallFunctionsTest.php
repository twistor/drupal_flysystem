<?php

namespace Drupal\Tests\flysystem\Unit;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Site\Settings;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\FlysystemFactory;
use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Twistor\FlysystemStreamWrapper;

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

    if (!defined('REQUIREMENT_ERROR')) {
      define('REQUIREMENT_ERROR', 2);
    }

    require_once dirname(dirname(dirname(__DIR__))) . '/flysystem.install';

    $this->factory = $this->prophesize(FlysystemFactory::class);

    $container = new ContainerBuilder();
    $container->set('flysystem_factory', $this->factory->reveal());
    $container->set('string_translation', $this->getStringTranslationStub());

    \Drupal::setContainer($container);
  }

  /**
   * Tests flysystem_requirements() handles update.
   */
  public function testFlysystemRequirementsHandlesUpdate() {
    $dependencies_exist = (int) class_exists(FlysystemStreamWrapper::class);

    $return = flysystem_requirements('update');
    $this->assertSame(1 - $dependencies_exist, count($return));
  }

  /**
   * Tests flysystem_requirements() handles install.
   */
  public function testFlysystemRequirementsHandlesInstall() {
    $dependencies_exist = (int) class_exists(FlysystemStreamWrapper::class);

    $return = flysystem_requirements('install');
    $this->assertSame(1 - $dependencies_exist, count($return));
  }

  /**
   * Tests flysystem_requirements() handles runtime.
   */
  public function testFlysystemRequirementsHandlesRuntime() {
    $dependencies_exist = (int) class_exists(FlysystemStreamWrapper::class);

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
   * Tests flysystem_requirements() detects invalid schemes.
   */
  public function testFlysystemRequirementsHandlesInvalidSchemes() {
    new Settings(['flysystem' => ['test_scheme' => []]]);
    $requirements = flysystem_requirements('update');

    $this->assertTrue(isset($requirements['flysystem_invalid_scheme']));
  }

  /**
   * Tests flysystem_install() calls ensure().
   */
  public function testFlysystemInstallCallsEnsure() {
    $this->factory->ensure()->shouldBeCalled();
    flysystem_install();
  }

}
