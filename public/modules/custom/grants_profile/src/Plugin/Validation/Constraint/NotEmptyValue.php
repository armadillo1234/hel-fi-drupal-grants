<?php

namespace Drupal\grants_profile\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Custom validation constraint.
 *
 * @Constraint(
 *   id = "NotEmptyValue",
 *   label = @Translation("Not empty value", context = "Validation"),
 *   type = "string"
 * )
 */
class NotEmptyValue extends Constraint {

  const IS_BLANK_ERROR = 'This value should not be blank.';

  /**
   * The message that will be shown if the value is not unique.
   *
   * @var string
   */
  public string $message = 'This value should not be blank.';

}
