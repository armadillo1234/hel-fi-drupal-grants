<?php

namespace Drupal\grants_budget_components\Plugin\DataType;

use Drupal\Core\TypedData\Plugin\DataType\Map;
use Drupal\grants_metadata\Plugin\DataType\DataFormatTrait;

/**
 * Grants Budget Income Other DataType.
 *
 * @DataType(
 * id = "grants_budget_income_other",
 * label = @Translation("Budget Income Other"),
 * definition_class = "\Drupal\grants_budget_components\TypedData\Definition\GrantsBudgetIncomeOtherDefinition"
 * )
 */
class GrantsBudgetIncomeOtherData extends Map {

  use DataFormatTrait;

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    $retval = parent::getValue();
    return $retval;
  }

  /**
   * Get values from parent.
   *
   * @return array
   *   The values.
   */
  public function getValues(): array {
    return $this->values;
  }

}
