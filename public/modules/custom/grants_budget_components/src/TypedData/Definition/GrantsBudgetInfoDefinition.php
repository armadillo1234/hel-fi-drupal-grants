<?php

namespace Drupal\grants_budget_components\TypedData\Definition;

use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\ListDataDefinition;

/**
 * Define Budget Cost Static data.
 */
class GrantsBudgetInfoDefinition extends ComplexDataDefinitionBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(): array {
    if (!isset($this->propertyDefinitions)) {
      $info = &$this->propertyDefinitions;

      $info['budget_static_income'] = ListDataDefinition::create('grants_budget_income_static')
        ->setSetting('fullItemValueCallback', [
          'service' => 'grants_budget_components.service',
          'method' => 'processBudgetStaticValues',
        ])
        ->setSetting('webformDataExtracter', [
          'service' => 'grants_budget_components.service',
          'method' => 'extractToWebformData',
        ])
        ->setSetting('jsonPath', [
          'incomeRowsArrayStatic',
        ]);

      $info['budget_other_income'] = ListDataDefinition::create('grants_budget_income_other')
        ->setSetting('fullItemValueCallback', [
          'service' => 'grants_budget_components.service',
          'method' => 'processBudgetOtherValues',
        ])
        ->setSetting('webformDataExtracter', [
          'service' => 'grants_budget_components.service',
          'method' => 'extractToWebformData',
        ])
        ->setSetting('jsonPath', [
          'otherIncomeRowsArrayStatic',
        ]);

      $info['budget_static_cost'] = ListDataDefinition::create('grants_budget_cost_static')
        ->setSetting('fullItemValueCallback', [
          'service' => 'grants_budget_components.service',
          'method' => 'processBudgetStaticValues',
        ])
        ->setSetting('webformDataExtracter', [
          'service' => 'grants_budget_components.service',
          'method' => 'extractToWebformData',
        ])
        ->setSetting('jsonPath', [
          'costRowsArrayStatic',
        ]);

      $info['budget_other_cost'] = ListDataDefinition::create('grants_budget_cost_other')
        ->setSetting('fullItemValueCallback', [
          'service' => 'grants_budget_components.service',
          'method' => 'processBudgetOtherValues',
        ])
        ->setSetting('webformDataExtracter', [
          'service' => 'grants_budget_components.service',
          'method' => 'extractToWebformData',
        ])
        ->setSetting('jsonPath', [
          'otherCostRowsArrayStatic',
        ]);

      $info['costGroupName'] = DataDefinition::create('string')
        ->setSetting('jsonPath', [
          'costGroupName',
        ])
        ->setSetting('defaultValue', 'general')
        ->setSetting('fullItemValueCallback', [
          'service' => 'grants_budget_components.service',
          'method' => 'processGroupName',
        ]);

      $info['incomeGroupName'] = DataDefinition::create('string')
        ->setSetting('jsonPath', [
          'incomeGroupName',
        ])
        ->setSetting('defaultValue', 'general')
        ->setSetting('fullItemValueCallback', [
          'service' => 'grants_budget_components.service',
          'method' => 'processGroupName',
        ]);

    }

    return $this->propertyDefinitions;
  }

}
