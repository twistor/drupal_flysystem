<?php

/**
 * @file
 * Contains \NoDrupal\Tests\flysystem\Unit\Flysystem\Adapter\MissingAdapterTest.
 */

namespace NoDrupal\Tests\flysystem\Unit\Flysystem\Adapter;

use Drupal\flysystem\Flysystem\Adapter\MissingAdapter;
use League\Flysystem\Config;

/**
 * @coversDefaultClass \Drupal\flysystem\Flysystem\Adapter\MissingAdapter
 * @group flysystem
 */
class MissingAdapterTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers \Drupal\flysystem\Flysystem\Adapter\MissingAdapter
   */
  public function test() {
    $adapter = new MissingAdapter();

    $this->assertFalse($adapter->copy('', ''));
    $this->assertFalse($adapter->createDir('', new Config()));
    $this->assertFalse($adapter->delete(''));
    $this->assertFalse($adapter->deleteDir(''));
    $this->assertInternalType('array', $adapter->listContents(''));
    $this->assertFalse($adapter->getMetadata(''));
    $this->assertFalse($adapter->getMimetype(''));
    $this->assertFalse($adapter->getSize(''));
    $this->assertFalse($adapter->getTimestamp(''));
    $this->assertFalse($adapter->getVisibility(''));
    $this->assertFalse($adapter->has(''));
    $this->assertFalse($adapter->setVisibility('', 'public'));
    $this->assertFalse($adapter->update('', '', new Config()));
    $this->assertFalse($adapter->updateStream('', NULL, new Config()));
    $this->assertFalse($adapter->read(''));
    $this->assertFalse($adapter->readStream(''));
    $this->assertFalse($adapter->rename('', ''));
    $this->assertFalse($adapter->write('', '', new Config()));
    $this->assertFalse($adapter->writeStream('', NULL, new Config()));
  }

}
