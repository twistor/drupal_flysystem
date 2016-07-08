<?php

namespace Drupal\Tests\flysystem\Unit\Asset;

use Drupal\Core\Site\Settings;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\Asset\SchemeExtensionTrait;

/**
 * @coversDefaultClass \Drupal\flysystem\Asset\SchemeExtensionTrait
 * @group flysystem
 */
class SchemeExtensionTraitTest extends UnitTestCase {

  /**
   * @covers ::getSchemeForExtension
   */
  public function test() {
    new Settings(['flysystem' => [
      'local' => ['serve_js' => TRUE, 'driver' => 'asdf'],
      'ftp' => ['serve_css' => TRUE],
    ]]);

    $trait = $this->getMockForTrait(SchemeExtensionTrait::class);
    $this->assertSame('local', $trait->getSchemeForExtension('js'));
    $this->assertSame('public', $trait->getSchemeForExtension('css'));
    $this->assertSame('public', $trait->getSchemeForExtension('jpg'));
  }

}
