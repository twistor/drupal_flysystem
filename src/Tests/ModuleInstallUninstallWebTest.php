<?php

namespace Drupal\flysystem\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests module installation and uninstallation.
 *
 * @group flysystem
 */
class ModuleInstallUninstallWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['flysystem'];

  /**
   * Tests installation and uninstallation.
   */
  protected function testInstallationAndUninstallation() {
    $module_handler = \Drupal::moduleHandler();
    $this->assertTrue($module_handler->moduleExists(reset(static::$modules)));

    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $module_installer */
    $module_installer = \Drupal::service('module_installer');

    $module_installer->uninstall(static::$modules);
    $this->assertFalse($module_handler->moduleExists(reset(static::$modules)));
  }

}
