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
          'premiseName',
        ]);

      $info['premiseAddress'] = DataDefinition::create('string')
        ->setLabel('Osoite')
        ->setSetting('jsonPath', [
          'premiseAddress',
        ]);

      $info['premiseType'] = DataDefinition::create('string')
        ->setLabel('Osoite')
        ->setSetting('jsonPath', [
          'premiseType',
        ]);

      $info['location'] = DataDefinition::create('string')
        ->setLabel('Sijainti')
        ->setSetting('jsonPath', [
          'location',
        ]);
      $info['streetAddress'] = DataDefinition::create('string')
        ->setLabel('Katuosoite')
        ->setSetting('jsonPath', [
          'streetAddress',
        ]);
      $info['address'] = DataDefinition::create('string')
        ->setLabel('Osoite')
        ->setSetting('jsonPath', [
          'address',
        ]);
      $info['postCode'] = DataDefinition::create('string')
        ->setLabel('Postinumero')
        ->setSetting('jsonPath', [
          'postCode',
        ]);
      $info['studentCount'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'studentCount',
        ]);
      $info['specialStudents'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'specialStudents',
        ]);
      $info['groupCount'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'groupCount',
        ]);
      $info['specialGroups'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'specialGroups',
        ]);
      $info['personnelCount'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'personnelCount',
        ]);
      $info['totalRent'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'totalRent',
        ]);
      $info['rentTimeBegin'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'rentTimeBegin',
        ]);
      $info['rentTimeEnd'] = DataDefinition::create('string')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'rentTimeEnd',
        ]);

      $info['premiseOwnerShip'] = DataDefinition::create('string')
        ->setLabel('description');

      $info['free'] = DataDefinition::create('boolean')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'free',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ]);

      $info['isOthersUse'] = DataDefinition::create('boolean')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'isOthersUse',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ]);

      $info['isOwnedByApplicant'] = DataDefinition::create('boolean')
        ->setLabel('description')
        ->setSetting('jsonPath', [
          'isOwnedByApplicant',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ]);
      $info['isOwnedByCity'] = DataDefinition::create('boolean')
        ->setLabel('Kyseessä on kaupungin omistama tila')
        ->setSetting('jsonPath', [
          'isOwnedByCity',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ]);
    }
    return $this->propertyDefinitions;
  }

}
