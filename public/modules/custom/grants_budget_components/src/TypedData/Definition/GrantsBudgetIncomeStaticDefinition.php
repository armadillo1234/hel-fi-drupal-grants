<?php

namespace Drupal\grants_budget_components\TypedData\Definition;

use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\grants_budget_components\Element\GrantsBudgetIncomeStatic;

/**
 * Define Budget Income Static data.
 */
class GrantsBudgetIncomeStaticDefinition extends ComplexDataDefinitionBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(): array {
    if (!isset($this->propertyDefinitions)) {
      $info = &$this->propertyDefinitions;

      $info['incomeGroupName'] = DataDefinition::create('string')
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'double',
        ]);

      $fieldNames = GrantsBudgetIncomeStatic::getFieldNames();

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
