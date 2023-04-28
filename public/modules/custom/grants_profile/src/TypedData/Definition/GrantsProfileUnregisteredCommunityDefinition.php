<?php

namespace Drupal\grants_profile\TypedData\Definition;

use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\ListDataDefinition;

/**
 * Define address data.
 */
class GrantsProfileUnregisteredCommunityDefinition extends ComplexDataDefinitionBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(): array {
    if (!isset($this->propertyDefinitions)) {
      $info = &$this->propertyDefinitions;

      $info['companyName'] = DataDefinition::create('string')
        ->setLabel('companyName')
        ->setReadOnly(TRUE)
        ->setSetting('jsonPath', [
          'grantsProfile',
          'profileInfoArray',
          'companyName',
        ]);

      $info['officials'] = ListDataDefinition::create('grants_profile_application_official')
        ->setRequired(FALSE)
        ->setSetting('jsonPath', ['grantsProfile', 'officialsArray'])
        ->setLabel('Officials of the unregistered community');

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
