<?php

namespace Drupal\Tests\flysystem\Unit\PathProcessor;

use Drupal\Core\Site\Settings;
use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\PathProcessor\LocalPathProcessor;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\flysystem\PathProcessor\LocalPathProcessor
 * @group flysystem
 */
class LocalPathProcessorTest extends UnitTestCase {

  /**
   * @var \Drupal\Core\PathProcessor\InboundPathProcessorInterface
   */
  protected $processor;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    new Settings(['flysystem' => [
      'testscheme' => [
        'driver' => 'local',
        'config' => ['root' => 'sites/default/files/flysystem'],
      ],
    ]]);

    $this->processor = new LocalPathProcessor('testscheme');
  }

  /**
   * @covers ::processInbound
   * @covers ::__construct
   */
  public function testProcessInboundIgnoresInvalidPaths() {
    $this->assertSame('beep', $this->processor->processInbound('beep', new Request()));
  }

  /**
   * @covers ::processInbound
   */
  public function testProcessInboundHandlesImageStyles() {
    $request = new Request();

    $this->assertSame('/sites/default/files/flysystem/styles/testscheme/small', $this->processor->processInbound('/sites/default/files/flysystem/styles/testscheme/small/image.jpg', $request));
    $this->assertSame($request->query->get('file'), 'image.jpg');

    $this->assertSame('/sites/default/files/flysystem/styles/testscheme/small', $this->processor->processInbound('/sites/default/files/flysystem/styles/testscheme/small/dir/image.jpg', $request));
    $this->assertSame($request->query->get('file'), 'dir/image.jpg');
  }

  /**
   * @covers ::processInbound
   */
  public function testProcessInboundHandlesSystemDownload() {
    $request = new Request();

    $this->assertSame('/sites/default/files/flysystem', $this->processor->processInbound('/sites/default/files/flysystem/file.txt', $request));
    $this->assertSame('file.txt', $request->query->get('file'));

    $this->assertSame('/sites/default/files/flysystem', $this->processor->processInbound('/sites/default/files/flysystem/a/b/c/file.txt', $request));
    $this->assertSame('a/b/c/file.txt', $request->query->get('file'));
  }

}
