<?php

/**
 * @file
 * Contains \Drupal\Tests\flysystem\Unit\PathProcessor\LocalPathProcessorTest.
 */

namespace Drupal\Tests\flysystem\Unit\PathProcessor;

use Drupal\Core\Site\Settings;
use Drupal\flysystem\PathProcessor\LocalPathProcessor;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\flysystem\PathProcessor\LocalPathProcessor
 * @group flysystem
 */
class LocalPathProcessorTest extends \PHPUnit_Framework_TestCase {

  public function test() {
    $settings = [
      'testscheme' => [
        'driver' => 'local',
        'config' => ['root' => 'sites/default/files/flysystem'],
      ],
    ];
    new Settings(['flysystem' => $settings]);

    $request = new Request();
    $processor = new LocalPathProcessor('testscheme');
    $this->assertSame('beep', $processor->processInbound('beep', $request));

    // Test image style.
    $this->assertSame('/sites/default/files/flysystem/styles/testscheme/small', $processor->processInbound('/sites/default/files/flysystem/styles/testscheme/small/image.jpg', $request));
    $this->assertSame($request->query->get('file'), 'image.jpg');
    $this->assertSame('/sites/default/files/flysystem/styles/testscheme/small', $processor->processInbound('/sites/default/files/flysystem/styles/testscheme/small/dir/image.jpg', $request));
    $this->assertSame($request->query->get('file'), 'dir/image.jpg');

    // Test system download.
    $request = new Request();
    $this->assertSame('/sites/default/files/flysystem', $processor->processInbound('/sites/default/files/flysystem/file.txt', $request));
    $this->assertSame('file.txt', $request->query->get('file'));

    // Test system download from sub-dir.
    $request = new Request();
    $this->assertSame('/sites/default/files/flysystem', $processor->processInbound('/sites/default/files/flysystem/a/b/c/file.txt', $request));
    $this->assertSame('a/b/c/file.txt', $request->query->get('file'));
  }

}
