<?php

namespace Drupal\grants_metadata\TypedData\Definition;

use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\ListDataDefinition;

/**
 * Define Yleisavustushakemus data.
 */
class KaskoYleisavustusDefinition extends ComplexDataDefinitionBase {

  use ApplicationDefinitionTrait;

  /**
   * Base data definitions for all.
   *
   * @return array
   *   Property definitions.
   */
  public function getPropertyDefinitions(): array {
    if (!isset($this->propertyDefinitions)) {

      $info = &$this->propertyDefinitions;

      foreach ($this->getBaseProperties() as $key => $property) {
        $info[$key] = $property;
      }

      $info['subventions'] = ListDataDefinition::create('grants_metadata_compensation_type')
        ->setLabel('compensationArray')
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'compensationArray',
        ]);

      $info['tilat'] = ListDataDefinition::create('grants_premises')
        ->setLabel('Tilat')
        ->setSetting('jsonPath', [
          'compensation',
          'activityInfo',
          'plannedPremisesArray',
        ])
        ->setSetting('usedFields', [
          'premiseName',
          'postCode',
          'isOwnedByCity',
        ])
        ->setSetting('fullItemValueCallback', [
          'service' => 'grants_premises.service',
          'method' => 'processPremises',
        ])
        ->setSetting('fieldsForApplication', [
          'premiseName',
          'isOwnedByCity',
          'postCode',
        ]);

    }
    return $this->propertyDefinitions;
  }

  /**
   * Override property definition.
   *
   * @param string $name
   *   Property name.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface|void|null
   *   Property definition.
   */
  public function getPropertyDefinition($name) {
    $retval = parent::getPropertyDefinition($name);
    return $retval;
  }

}
