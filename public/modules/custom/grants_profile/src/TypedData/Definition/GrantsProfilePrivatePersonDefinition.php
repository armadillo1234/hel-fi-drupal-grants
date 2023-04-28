<?php

namespace Drupal\grants_profile\TypedData\Definition;

use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\ListDataDefinition;

/**
 * Define address data.
 */
class GrantsProfilePrivatePersonDefinition extends ComplexDataDefinitionBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(): array {
    if (!isset($this->propertyDefinitions)) {
      $info = &$this->propertyDefinitions;

      $info['phone_number'] = DataDefinition::create('string')
        ->setLabel('Phone Number')
        ->setRequired(TRUE)
        ->setSetting('jsonPath', [
          'grantsProfile',
          'profileInfoArray',
          'companyNameShort',
        ]);

      $info['email'] = DataDefinition::create('string')
        ->setLabel('Email address')
        ->setSetting('jsonPath', ['grantsProfile', 'profileInfoArray', 'email'])
        ->addConstraint('Email')
        ->addConstraint('NotBlank')
        ->setRequired(TRUE);

      $info['addresses'] = ListDataDefinition::create('grants_profile_address')
        ->setRequired(TRUE)
        ->setSetting('jsonPath', ['grantsProfile', 'addressesArray'])
        ->setLabel('Addresses');

      $info['bankAccounts'] = ListDataDefinition::create('grants_profile_bank_account')
        ->setRequired(TRUE)
        ->setSetting('jsonPath', ['grantsProfile', 'bankAccountsArray'])
        ->setLabel('Bank account numbers');

    }
    return $this->propertyDefinitions;
  }

}
