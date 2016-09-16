<?php

namespace Drupal\Tests\flysystem\Unit\EventSubscriber;

use Drupal\Tests\UnitTestCase;
use Drupal\flysystem\EventSubscriber\EnsureSubscriber;
use Drupal\flysystem\Event\EnsureEvent;
use Drupal\flysystem\Event\FlysystemEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @coversDefaultClass \Drupal\flysystem\EventSubscriber\EnsureSubscriber
 * @covers \Drupal\flysystem\EventSubscriber\EnsureSubscriber
 *
 * @group flysystem
 */
class EnsureSubscriberTest extends UnitTestCase {

  /**
   * Tests that the event subscriber logs ensure() calls.
   */
  public function testLoggingHappens() {
    $logger = $this->prophesize(LoggerInterface::class);
    $dispatcher = $this->getMock(EventDispatcherInterface::class);
    $logger->log('severity', 'message', array('context'))->shouldBeCalled();

    $event = new EnsureEvent('scheme', 'severity', 'message', array('context'));

    $subscriber = new EnsureSubscriber($logger->reveal());

    $subscriber->onEnsure($event, FlysystemEvents::ENSURE, $dispatcher);
  }

  /**
   * Tests that the ENSURE event is registered.
   */
  public function testSubscribedEvents() {
    $result = EnsureSubscriber::getSubscribedEvents();

    $this->assertTrue(isset($result[FlysystemEvents::ENSURE]));
  }

}
