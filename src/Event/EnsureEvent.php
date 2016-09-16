<?php

namespace Drupal\flysystem\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * The event fired for every result from an ensure() call.
 */
class EnsureEvent extends Event {

  /**
   * The log context.
   *
   * @var array
   */
  protected $context;

  /**
   * The log message.
   *
   * @var string
   */
  protected $message;

  /**
   * The scheme of the item being logged.
   *
   * @var string
   */
  protected $scheme;

  /**
   * The severity of the message being logged.
   *
   * @var int
   */
  protected $severity;

  /**
   * Constructs an EnsureEvent object.
   *
   * @param string $scheme
   *   The scheme.
   * @param int $severity
   *   The severity.
   * @param string $message
   *   The message.
   * @param array $context
   *   The context for the message.
   */
  public function __construct($scheme, $severity, $message, array $context) {
    $this->scheme = $scheme;
    $this->severity = $severity;
    $this->message = $message;
    $this->context = $context;
  }

  /**
   * Returns the context.
   *
   * @return array
   */
  public function getContext() {
    return $this->context;
  }

  /**
   * Returns the message.
   *
   * @return string
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * Returns the scheme.
   *
   * @return string
   */
  public function getScheme() {
    return $this->scheme;
  }

  /**
   * Returns the severity.
   *
   * @return int
   */
  public function getSeverity() {
    return $this->severity;
  }

}
