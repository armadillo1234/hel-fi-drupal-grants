<?php

namespace Drupal\grants_budget_components\TypedData\Definition;

use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\grants_budget_components\Element\GrantsBudgetCostStatic;

/**
 * Define Budget Cost Static data.
 */
class GrantsBudgetCostStaticDefinition extends ComplexDataDefinitionBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(): array {
    if (!isset($this->propertyDefinitions)) {
      $info = &$this->propertyDefinitions;

      $info['costGroupName'] = DataDefinition::create('string');

      $fieldNames = GrantsBudgetCostStatic::getFieldNames();

      foreach ($fieldNames as $fieldKey => $fieldValue) {
        $info[$fieldKey] = DataDefinition::create('string')
          ->setLabel($fieldValue)
          ->setSetting('typeOverride', [
            'dataType' => 'string',
            'jsonType' => 'double',
          ]);
      }

    }
    return $this->propertyDefinitions;
  }

}
