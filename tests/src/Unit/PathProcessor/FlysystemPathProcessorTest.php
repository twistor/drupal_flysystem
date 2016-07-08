<?php

namespace Drupal\Tests\flysystem\Unit\PathProcessor;

use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\PathProcessor\FlysystemPathProcessor;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\flysystem\PathProcessor\FlysystemPathProcessor
 * @group flysystem
 */
class FlysystemPathProcessorTest extends UnitTestCase {

  /**
   * @covers ::processInbound
   */
  public function testCorrectPathsAreProccessed() {
    $processor = new FlysystemPathProcessor();
    $this->assertSame('beep', $processor->processInbound('beep', new Request()));
    $this->assertSame('/_flysystem/scheme', $processor->processInbound('/_flysystem/scheme', new Request()));
  }

  /**
   * @covers ::processInbound
   */
  public function testImageStylesAreProccessed() {
    $request = new Request();
    $processor = new FlysystemPathProcessor();
    $this->assertSame('/_flysystem/styles/scheme/small', $processor->processInbound('/_flysystem/scheme/styles/scheme/small/image.jpg', $request));
    $this->assertSame($request->query->get('file'), 'image.jpg');
    $this->assertSame('/_flysystem/styles/scheme/small', $processor->processInbound('/_flysystem/scheme/styles/scheme/small/dir/image.jpg', $request));
    $this->assertSame($request->query->get('file'), 'dir/image.jpg');
  }

  /**
   * @covers ::processInbound
   */
  public function testDownloadPathsAreProccessed() {
    $request = new Request();
    $processor = new FlysystemPathProcessor();
    $this->assertSame('/_flysystem/scheme', $processor->processInbound('/_flysystem/scheme/file.txt', $request));
    $this->assertSame('file.txt', $request->query->get('file'));
  }

  /**
   * @covers ::processInbound
   */
  public function testDownloadPathsInSubDirsAreProccessed() {
    $request = new Request();
    $processor = new FlysystemPathProcessor();
    $this->assertSame('/_flysystem/scheme', $processor->processInbound('/_flysystem/scheme/a/b/c/file.txt', $request));
    $this->assertSame('a/b/c/file.txt', $request->query->get('file'));
  }

}
