<?php

namespace Drupal\flysystem;

/**
 * Stops a class from being serialized.
 */
trait SerializationStopperTrait {

  /**
   * Prevents the class from being serialized.
   */
  public function __sleep() {
    $message = sprintf('%s can not be serialized. This probably means you are serializing an object that has an indirect reference to the %s object. Adjust your code so that is not necessary.', __CLASS__, __CLASS__);
    throw new \LogicException($message);
  }

}
