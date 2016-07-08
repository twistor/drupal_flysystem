<?php

namespace Drupal\flysystem\Logger;

use Drupal\Core\Logger\RfcLogLevel;

/**
 * Converts various log levels.
 */
class Convert {

  /**
   * Coverts from RFC 5424 to requirements error constants.
   *
   * @param int $level
   *   The RFC log value.
   *
   * @return int
   *   The requirements error value.
   */
  public static function rfcToHookRequirements($level) {
    if ($level <= RfcLogLevel::ERROR) {
      return REQUIREMENT_ERROR;
    }

    if ($level == RfcLogLevel::WARNING) {
      return REQUIREMENT_WARNING;
    }

    if ($level == RfcLogLevel::NOTICE || $level == RfcLogLevel::INFO) {
      return REQUIREMENT_INFO;
    }

    return REQUIREMENT_OK;
  }

}
