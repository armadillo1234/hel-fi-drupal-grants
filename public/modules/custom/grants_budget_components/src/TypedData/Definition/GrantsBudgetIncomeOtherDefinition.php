<?php

namespace Drupal\grants_budget_components\TypedData\Definition;

use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Define Budget Cost Static data.
 */
class GrantsBudgetIncomeOtherDefinition extends ComplexDataDefinitionBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(): array {
    if (!isset($this->propertyDefinitions)) {
      $info = &$this->propertyDefinitions;

      $info['incomeGroupName'] = DataDefinition::create('string');
      $info['label'] = DataDefinition::create('string');
      $info['value'] = DataDefinition::create('string');
    }

    return $this->propertyDefinitions;
  }

}
