<?php

namespace Drupal\grants_budget_components\Plugin\DataType;

use Drupal\Core\TypedData\Plugin\DataType\Map;
use Drupal\grants_metadata\Plugin\DataType\DataFormatTrait;

/**
 * Grants Budget Cost Other DataType.
 *
 * @DataType(
 * id = "grants_budget_info",
 * label = @Translation("Budget info"),
 * definition_class = "\Drupal\grants_budget_components\TypedData\Definition\GrantsBudgetInfoDefinition"
 * )
 */
class GrantsBudgetInfoData extends Map {

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
