<?php

namespace Drupal\flysystem\EventSubscriber;

use Drupal\flysystem\Event\EnsureEvent;
use Drupal\flysystem\Event\FlysystemEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener that listens to Flysystem ensure() calls.
 */
class EnsureSubscriber implements EventSubscriberInterface {

  /**
   * The logger to use.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[FlysystemEvents::ENSURE][] = 'onEnsure';

    return $events;
  }

  /**
   * Constructs an EnsureSubscriber object.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(LoggerInterface $logger) {
    $this->logger = $logger;
  }

  /**
   * Responds to FlysystemFactory::ensure().
   */
  public function onEnsure(EnsureEvent $event, $event_name, EventDispatcherInterface $dispatcher) {
    $this->logger->log(
      $event->getSeverity(),
      $event->getMessage(),
      $event->getContext()
    );
  }

}
