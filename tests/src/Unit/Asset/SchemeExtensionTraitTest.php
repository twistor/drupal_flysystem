<?php

/**
 * @file
 * Contains \Drupal\Tests\flysystem\Unit\Asset\SchemeExtensionTraitTest.
 */

namespace Drupal\Tests\flysystem\Unit\Asset;

use Drupal\Core\Site\Settings;

/**
 * @coversDefaultClass \Drupal\flysystem\Asset\SchemeExtensionTrait
 * @group flysystem
 */
class SchemeExtensionTraitTest extends \PHPUnit_Framework_TestCase {

  public function test() {
    new Settings(['flysystem' => [
      'local' => ['serve_js' => TRUE],
      'ftp' => ['serve_css' => TRUE],
    ]]);

    $trait = $this->getMockForTrait('Drupal\flysystem\Asset\SchemeExtensionTrait');
    $this->assertSame('local', $trait->getSchemeForExtension('js'));
    $this->assertSame('ftp', $trait->getSchemeForExtension('css'));
    $this->assertSame('public', $trait->getSchemeForExtension('jpg'));
  }

}
