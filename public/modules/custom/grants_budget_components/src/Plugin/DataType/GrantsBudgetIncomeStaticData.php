<?php

namespace Drupal\grants_budget_components\Plugin\DataType;

use Drupal\Core\TypedData\Plugin\DataType\Map;
use Drupal\grants_metadata\Plugin\DataType\DataFormatTrait;

/**
 * Grants Budget Income Static DataType.
 *
 * @DataType(
 * id = "grants_budget_income_static",
 * label = @Translation("Budget Income Static"),
 * definition_class = "\Drupal\grants_budget_components\TypedData\Definition\GrantsBudgetIncomeStaticDefinition"
 * )
 */
class GrantsBudgetIncomeStaticData extends Map {

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
