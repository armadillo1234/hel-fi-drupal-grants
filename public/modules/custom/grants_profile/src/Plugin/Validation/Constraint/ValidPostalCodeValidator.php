<?php

namespace Drupal\grants_profile\Plugin\Validation\Constraint;

use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ValidIban constraint.
 */
class ValidPostalCodeValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, $constraint) {
    if ($value !== NULL && !$this->isValidPostalCode($value)) {

      $this->context->addViolation($constraint->notValidPostalCode, ['%value' => $value]);
    }
  }

  /**
   * Validate postal code.
   *
   * @param string|null $value
   *   Postal code.
   *
   * @return bool
   *   Is postal code valid.
   */
  private function isValidPostalCode(?string $value): bool {
    return (bool) preg_match("/^(FI-)?[0-9]{5}$/", $value);
  }

}
