<?php

namespace Drupal\grants_metadata\TypedData\Definition;

use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;
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

      $info['members_applicant_person_local'] = DataDefinition::create('string')
        ->setLabel('activitiesInfoArray=>membersApplicantPersonLocal')
        ->setSetting('defaultValue', "")
        ->setSetting('jsonPath', [
          'compensation',
          'activitiesInfoArray',
          'membersApplicantPersonLocal',
        ]);

      $info['members_applicant_person_global'] = DataDefinition::create('string')
        ->setLabel('activitiesInfoArray=>membersApplicantPersonGlobal')
        ->setSetting('defaultValue', "")
        ->setSetting('jsonPath', [
          'compensation',
          'activitiesInfoArray',
          'membersApplicantPersonGlobal',
        ]);

      $info['members_applicant_community_local'] = DataDefinition::create('string')
        ->setLabel('activitiesInfoArray=>membersApplicantCommunityLocal')
        ->setSetting('defaultValue', "")
        ->setSetting('jsonPath', [
          'compensation',
          'activitiesInfoArray',
          'membersApplicantCommunityLocal',
        ]);

      $info['members_applicant_community_global'] = DataDefinition::create('string')
        ->setLabel('activitiesInfoArray=>membersApplicantCommunityGlobal')
        ->setSetting('jsonPath', [
          'compensation',
          'activitiesInfoArray',
          'membersApplicantCommunityGlobal',
        ]);

      $info['subventions'] = ListDataDefinition::create('grants_metadata_compensation_type')
        ->setLabel('compensationArray')
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'compensationArray',
        ]);

      $info['compensation_purpose'] = DataDefinition::create('string')
        ->setLabel('')
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'generalInfoArray',
          'purpose',
        ]);

      $info['compensation_boolean'] = DataDefinition::create('string')
        ->setLabel('compensationPreviousYear')
        ->setSetting('defaultValue', FALSE)
        ->setSetting('typeOverride', [
          'dataType' => 'string',
          'jsonType' => 'bool',
        ])
        ->setSetting('jsonPath', [
          'compensation',
          'compensationInfo',
          'generalInfoArray',
          'compensationPreviousYear',
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
