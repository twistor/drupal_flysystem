<?php

/**
 * @file
 * Contains \Drupal\Tests\flysystem\Unit\PathProcessor\FlysystemPathProcessorTest.
 */

namespace Drupal\Tests\flysystem\Unit\PathProcessor;

use Drupal\flysystem\PathProcessor\FlysystemPathProcessor;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\flysystem\PathProcessor\FlysystemPathProcessor
 * @group flysystem
 */
class FlysystemPathProcessorTest extends \PHPUnit_Framework_TestCase {

  public function test() {
    $request = new Request();
    $processor = new FlysystemPathProcessor();
    $this->assertSame('beep', $processor->processInbound('beep', $request));
    $this->assertSame('/_flysystem/scheme', $processor->processInbound('/_flysystem/scheme', $request));

    // Test image style.
    $this->assertSame('/_flysystem/styles/scheme/small', $processor->processInbound('/_flysystem/scheme/styles/scheme/small/image.jpg', $request));
    $this->assertSame($request->query->get('file'), 'image.jpg');
    $this->assertSame('/_flysystem/styles/scheme/small', $processor->processInbound('/_flysystem/scheme/styles/scheme/small/dir/image.jpg', $request));
    $this->assertSame($request->query->get('file'), 'dir/image.jpg');

    // Test system download.
    $request = new Request();
    $this->assertSame('/_flysystem/scheme', $processor->processInbound('/_flysystem/scheme/file.txt', $request));
    $this->assertSame('file.txt', $request->query->get('file'));

    // Test system download from sub-dir.
    $request = new Request();
    $this->assertSame('/_flysystem/scheme', $processor->processInbound('/_flysystem/scheme/a/b/c/file.txt', $request));
    $this->assertSame('a/b/c/file.txt', $request->query->get('file'));
  }

}
