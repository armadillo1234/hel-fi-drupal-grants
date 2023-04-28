<?php

namespace Drupal\grants_premises\TypedData\Definition;

use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Define Application official data.
 */
class GrantsPremisesDefinition extends ComplexDataDefinitionBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(): array {
    if (!isset($this->propertyDefinitions)) {
      $info = &$this->propertyDefinitions;

      $info['premiseName'] = DataDefinition::create('string')
        ->setLabel('Nimi')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'premiseName',
        ]);

      $info['premiseAddress'] = DataDefinition::create('string')
        ->setLabel('Osoite')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'premiseAddress',
        ]);

      $info['location'] = DataDefinition::create('string')
        ->setLabel('Sijainti')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'location',
        ]);
      $info['streetAddress'] = DataDefinition::create('string')
        ->setLabel('Katuosoite')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'streetAddress',
        ]);
      $info['address'] = DataDefinition::create('string')
        ->setLabel('Osoite')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'address',
        ]);
      $info['postCode'] = DataDefinition::create('string')
        ->setLabel('Postinumero')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'postCode',
        ]);
      $info['studentCount'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'studentCount',
        ]);
      $info['specialStudents'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'specialStudents',
        ]);
      $info['groupCount'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'groupCount',
        ]);
      $info['specialGroups'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'specialGroups',
        ]);
      $info['personnelCount'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'personnelCount',
        ]);
      $info['totalRent'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'totalRent',
        ]);
      $info['rentTimeBegin'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'rentTimeBegin',
        ]);
      $info['rentTimeEnd'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'rentTimeEnd',
        ]);
      $info['free'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'free',
        ]);
      $info['isOthersUse'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'isOthersUse',
        ]);
      $info['premiseOwnerShip'] = DataDefinition::create('string')
        ->setLabel('description');

      $info['isOwnedByApplicant'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'rentTimeEnd',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ]);
      $info['isOwnedByCity'] = DataDefinition::create('string')
        ->setLabel('Kyseessä on kaupungin omistama tila')
        ->setSetting('jsonPath', [
          'activityInfo',
          'plannedPremisesArray',
          'rentTimeEnd',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ]);
    }
    return $this->propertyDefinitions;
  }

}
