<?php

namespace Drupal\grants_metadata;

/**
 * Provide useful helper for converting values.
 */
class GrantsConverterService {

  const DEFAULT_DATETIME_FORMAT = 'c';

  /**
   * Format dates to a given or default format.
   *
   * @param string $value
   *   Input value.
   * @param array $arguments
   *   Arguments, dateFormat is used.
   *
   * @return string
   *   Formatted datetime string.
   */
  public function convertDates(string $value, array $arguments): string {

    try {
      $dateObject = new \DateTime($value);
      if (isset($arguments['dateFormat'])) {
        $retval = $dateObject->format($arguments['dateFormat']);
      }
      else {
        $retval = $dateObject->format(self::DEFAULT_DATETIME_FORMAT);
      }

    }
    catch (\Exception $e) {
      $retval = '';
    }

    return $retval;
  }

}
