<?php

namespace Drupal\grants_metadata\TypedData\Definition;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\ListDataDefinition;
use Drupal\Core\TypedData\MapDataDefinition;
use Drupal\grants_applicant_info\TypedData\Definition\ApplicantInfoDefinition;

/**
 * Base class for data typing & mapping.
 */
trait ApplicationDefinitionTrait {

  /**
   * Base data definitions for all.
   */
  public function getBaseProperties(): array {

    /** @var \Drupal\grants_profile\GrantsProfileService $grantsProfileService */
    $grantsProfileService = \Drupal::service('grants_profile.service');
    $applicantType = $grantsProfileService->getApplicantType();

    $info['hakijan_tiedot'] = ApplicantInfoDefinition::create('applicant_info')
      // ->setRequired(TRUE)
      ->setSetting('jsonPath', ['compensation', 'applicantOfficialsArray'])
      ->setSetting('defaultValue', [])
      ->setLabel('Applicant info')
      ->setSetting('propertyStructureCallback', [
        'service' => 'grants_applicant_info.service',
        'method' => 'processApplicantInfo',
      ])
      ->setSetting('webformDataExtracter', [
        'service' => 'grants_applicant_info.service',
        'method' => 'extractDataForWebform',
      ])
      ->setSetting('fieldsForApplication', [
        'premiseName',
        'isOwnedByCity',
        'postCode',
      ]);

    // Both communities.
    if ($applicantType === 'registered_community' || $applicantType === 'unregistered_community') {

      $info['email'] = DataDefinition::create('email')
        ->setLabel('Sähköpostiosoite')
        ->setSetting('jsonPath', [
          'compensation',
          'applicantInfoArray',
          'email',
        ])
        ->setSetting('typeOverride', [
          'dataType' => 'email',
          'jsonType' => 'string',
        ])
        ->addConstraint('Email');

      $info['community_officials'] = ListDataDefinition::create('grants_profile_application_official')
        // ->setRequired(TRUE)
        ->setSetting('jsonPath', ['compensation', 'applicantOfficialsArray'])
        ->setSetting('defaultValue', [])
        ->setLabel('applicantOfficialsArray');

      $info['contact_person'] = DataDefinition::create('string')
        ->setLabel('currentAddressInfoArray=>contactPerson')
        ->setSetting('jsonPath', [
          'compensation',
          'currentAddressInfoArray',
          'contactPerson',
        ]);

      $info['contact_person_phone_number'] = DataDefinition::create('string')
        ->setLabel('Contact person phone')
        ->setSetting('jsonPath', [
          'compensation',
          'currentAddressInfoArray',
          'phoneNumber',
        ]);

      $info['community_street'] = DataDefinition::create('string')
        ->setLabel('Community street')
        ->setSetting('jsonPath', [
          'compensation',
          'currentAddressInfoArray',
          'street',
        ])
        ->setSetting('formSettings', [
          'formElement' => 'community_address',
          'formError' => 'You must select address',
        ]);

      $info['community_city'] = DataDefinition::create('string')
        // ->setRequired(TRUE)
        ->setLabel('Community city')
        ->setSetting('jsonPath', [
          'compensation',
          'currentAddressInfoArray',
          'city',
        ])
        ->setSetting('formErrorElement', [
          'formElement' => 'community_address',
          'formError' => 'You must select address',
        ]);

      $info['community_post_code'] = DataDefinition::create('string')
        ->setLabel('Community postal code')
        ->setSetting('jsonPath', [
          'compensation',
          'currentAddressInfoArray',
          'postCode',
        ])
        ->setSetting('formErrorElement', [
          'formElement' => 'community_address',
          'formError' => 'You must select address',
        ])
        ->addConstraint('ValidPostalCode');

      $info['community_country'] = DataDefinition::create('string')
        ->setLabel('Community country')
        ->setSetting('jsonPath', [
          'compensation',
          'currentAddressInfoArray',
          'country',
        ])
        ->setSetting('formErrorElement', [
          'formElement' => 'community_address',
          'formError' => 'You must select address',
        ])
        ->setSetting('defaultValue', 'Suomi');
    }

    $info['application_type'] = DataDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel('Application type')
      ->setSetting('jsonPath', [
        'compensation',
        'applicationInfoArray',
        'applicationType',
      ]);
    // ->addConstraint('NotBlank')
    $info['application_type_id'] = DataDefinition::create('string')
      ->setLabel('Application type id')
      ->setSetting('jsonPath', [
        'compensation',
        'applicationInfoArray',
        'applicationTypeID',
      ]);

    $info['form_timestamp'] = DataDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel('formTimeStamp')
      ->setSetting('jsonPath', [
        'compensation',
        'applicationInfoArray',
        'formTimeStamp',
      ]);

    $info['form_timestamp_created'] = DataDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel('createdFormTimeStamp')
      ->setSetting('jsonPath', [
        'compensation',
        'applicationInfoArray',
        'createdFormTimeStamp',
      ]);

    $info['form_timestamp_submitted'] = DataDefinition::create('string')
      ->setRequired(FALSE)
      ->setLabel('submittedFormTimeStamp')
      ->setSetting('jsonPath', [
        'compensation',
        'applicationInfoArray',
        'submittedFormTimeStamp',
      ]);

    $info['application_number'] = DataDefinition::create('string')
      // ->setRequired(TRUE)
      ->setLabel('applicationNumber')
      ->setSetting('jsonPath', [
        'compensation',
        'applicationInfoArray',
        'applicationNumber',
      ]);
    $info['status'] = DataDefinition::create('string')
      ->setLabel('Status')
      ->setSetting('jsonPath', [
        'compensation',
        'applicationInfoArray',
        'status',
      ]);
    $info['acting_year'] = DataDefinition::create('string')
      ->setLabel('Acting year')
      ->setSetting('defaultValue', "")
      ->setSetting('jsonPath', [
        'compensation',
        'applicationInfoArray',
        'actingYear',
      ]);

    $info['account_number'] = DataDefinition::create('string')
      ->setLabel('accountNumber')
      ->setSetting('jsonPath', [
        'compensation',
        'bankAccountArray',
        'accountNumber',
      ])
      ->addConstraint('NotBlank');

    $info['myonnetty_avustus'] = ListDataDefinition::create('grants_metadata_other_compensation')
      ->setLabel('Myönnetty avustus')
      ->setSetting('defaultValue', [])
      ->setSetting('jsonPath', [
        'compensation',
        'otherCompensationsInfo',
        'otherCompensationsArray',
      ])
      ->setSetting('requiredInJson', TRUE);

    $info['haettu_avustus_tieto'] = ListDataDefinition::create('grants_metadata_other_compensation')
      ->setLabel('Haettu avustus')
      ->setSetting('defaultValue', [])
      ->setSetting('jsonPath', [
        'compensation',
        'otherCompensationsInfo',
        'otherAppliedCompensationsArray',
      ]);

    $info['myonnetty_avustus_total'] = DataDefinition::create('float')
      ->setLabel('Myönnetty avustus total')
      ->setSetting('defaultValue', 0)
      ->setSetting('typeOverride', [
        'dataType' => 'string',
        'jsonType' => 'double',
      ])
      ->setSetting('valueCallback', [
        '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
        'convertToFloat',
      ])
      ->setSetting('jsonPath', [
        'compensation',
        'otherCompensationsInfo',
        'otherCompensationsInfoArray',
        'otherCompensationsTotal',
      ])
      ->addConstraint('NotBlank');

    $info['haettu_avustus_tieto_total'] = DataDefinition::create('float')
      ->setLabel('Haettu avustus total')
      ->setSetting('defaultValue', 0)
      ->setSetting('typeOverride', [
        'dataType' => 'string',
        'jsonType' => 'double',
      ])
      ->setSetting('jsonPath', [
        'compensation',
        'otherCompensationsInfo',
        'otherCompensationsInfoArray',
        'otherAppliedCompensationsTotal',
      ])
      ->setSetting('valueCallback', [
        '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
        'convertToFloat',
      ])
      ->addConstraint('NotBlank');

    $info['benefits_loans'] = DataDefinition::create('string')
      ->setLabel('Loans')
      ->setSetting('defaultValue', "")
      ->setSetting('jsonPath', [
        'compensation',
        'benefitsInfoArray',
        'loans',
      ]);

    $info['benefits_premises'] = DataDefinition::create('string')
      ->setLabel('Premises')
      ->setSetting('defaultValue', "")
      ->setSetting('jsonPath', [
        'compensation',
        'benefitsInfoArray',
        'premises',
      ]);

    $info['fee_person'] = DataDefinition::create('string')
      ->setLabel('activitiesInfoArray=>feePerson')
      ->setSetting('jsonPath', [
        'compensation',
        'activitiesInfoArray',
        'feePerson',
      ])
      ->setSetting('valueCallback', [
        '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
        'convertToFloat',
      ])
      ->addConstraint('NotBlank');

    $info['fee_community'] = DataDefinition::create('string')
      ->setLabel('activitiesInfoArray=>feeCommunity')
      ->setSetting('jsonPath', [
        'compensation',
        'activitiesInfoArray',
        'feeCommunity',
      ])
      ->setSetting('valueCallback', [
        '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
        'convertToFloat',
      ])
      ->addConstraint('NotBlank');

    $info['business_purpose'] = DataDefinition::create('string')
      ->setLabel('businessPurpose')
      ->setSetting('jsonPath', [
        'compensation',
        'activitiesInfoArray',
        'businessPurpose',
      ])
      ->setSetting('defaultValue', '');

    $info['community_practices_business'] = DataDefinition::create('string')
      ->setLabel('communityPracticesBusiness')
      ->setSetting('defaultValue', FALSE)
      ->setSetting('jsonPath', [
        'compensation',
        'activitiesInfoArray',
        'communityPracticesBusiness',
      ])
      ->setSetting('typeOverride', [
        'dataType' => 'string',
        'jsonType' => 'bool',
      ])
      ->setSetting('defaultValue', FALSE);

    $info['additional_information'] = DataDefinition::create('string')
      ->setLabel('additionalInformation')
      ->setSetting('jsonPath', ['compensation', 'additionalInformation'])
      ->setSetting('defaultValue', "");

    // Sender details.
    // @todo Maybe move sender info to custom definition?
    $info['sender_firstname'] = DataDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel('firstname')
      ->setSetting('jsonPath', [
        'compensation',
        'senderInfoArray',
        'firstname',
      ]);
    $info['sender_lastname'] = DataDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel('lastname')
      ->setSetting('jsonPath', [
        'compensation',
        'senderInfoArray',
        'lastname',
      ]);
    // @todo Validate person id?
    $info['sender_person_id'] = DataDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel('personID')
      ->setSetting('jsonPath', [
        'compensation',
        'senderInfoArray',
        'personID',
      ]);
    $info['sender_user_id'] = DataDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel('userID')
      ->setSetting('jsonPath', ['compensation', 'senderInfoArray', 'userID']);
    $info['sender_email'] = DataDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel('Email')
      ->setSetting('jsonPath', ['compensation', 'senderInfoArray', 'email']);

    // Attachments.
    $info['attachments'] = ListDataDefinition::create('grants_metadata_attachment')
      ->setLabel('Attachments')
      ->setSetting('jsonPath', ['attachmentsInfo', 'attachmentsArray']);

    $info['extra_info'] = DataDefinition::create('string')
      ->setLabel('Extra Info')
      ->setSetting('jsonPath', [
        'attachmentsInfo',
        'generalInfoArray',
        'extraInfo',
      ]);

    $info['form_update'] = DataDefinition::create('boolean')
      ->setRequired(TRUE)
      ->setLabel('formUpdate')
      ->setSetting('jsonPath', ['formUpdate'])
      ->setSetting('typeOverride', [
        'dataType' => 'string',
        'jsonType' => 'bool',
      ])
      ->setSetting('defaultValue', FALSE);

    $info['status_updates'] = MapDataDefinition::create()
      ->setSetting('valueCallback', [
        '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
        'cleanUpArrayValues',
      ])
      ->setPropertyDefinition(
        'caseId',
        DataDefinition::create('string')
          ->setRequired(FALSE)
          ->setSetting('jsonPath', ['statusUpdates', 'caseId'])
      )
      ->setPropertyDefinition(
        'citizenCaseStatus',
        DataDefinition::create('string')
          ->setRequired(FALSE)
          ->setSetting('jsonPath', ['statusUpdates', 'citizenCaseStatus'])
      )
      ->setPropertyDefinition(
        'eventType',
        DataDefinition::create('string')
          ->setRequired(FALSE)
          ->setSetting('jsonPath', ['statusUpdates', 'eventType'])
      )
      ->setPropertyDefinition(
        'eventCode',
        DataDefinition::create('integer')
          ->setRequired(FALSE)
          ->setSetting('jsonPath', ['statusUpdates', 'eventCode'])
      )
      ->setPropertyDefinition(
        'eventSource',
        DataDefinition::create('string')
          ->setRequired(FALSE)
          ->setSetting('jsonPath', ['statusUpdates', 'eventSource'])
      )
      ->setPropertyDefinition(
        'timeUpdated',
        DataDefinition::create('string')
          ->setRequired(FALSE)
          ->setSetting('jsonPath', ['statusUpdates', 'timeUpdated'])
      )
      ->setSetting('jsonPath', ['statusUpdates'])
      ->setRequired(FALSE);

    $info['events'] = MapDataDefinition::create()
      ->setSetting('valueCallback', [
        '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
        'cleanUpArrayValues',
      ])
      ->setPropertyDefinition(
        'caseId',
        DataDefinition::create('string')
          ->setRequired(FALSE)
          ->setSetting('jsonPath', ['events', 'caseId'])
      )
      ->setPropertyDefinition(
        'eventType',
        DataDefinition::create('string')
          ->setRequired(FALSE)
          ->setSetting('jsonPath', ['events', 'eventType'])
      )
      ->setPropertyDefinition(
        'eventCode',
        DataDefinition::create('integer')
          ->setRequired(FALSE)
          ->setSetting('jsonPath', ['events', 'eventCode'])
      )
      ->setPropertyDefinition(
        'eventSource',
        DataDefinition::create('string')
          ->setRequired(FALSE)
          ->setSetting('jsonPath', ['events', 'eventSource'])
      )
      ->setPropertyDefinition(
        'timeUpdated',
        DataDefinition::create('string')
          ->setRequired(FALSE)
          ->setSetting('jsonPath', ['events', 'timeUpdated'])
      )
      ->setSetting('jsonPath', ['events'])
      ->setRequired(FALSE);

    $info['messages'] = MapDataDefinition::create()
      ->setSetting('valueCallback', [
        '\Drupal\grants_handler\Plugin\WebformHandler\GrantsHandler',
        'cleanUpArrayValues',
      ])
      ->setPropertyDefinition(
        'caseId',
        DataDefinition::create('string')
          ->setRequired(FALSE)
          ->setSetting('jsonPath', ['messages', 'caseId'])
      )
      ->setPropertyDefinition(
        'messageId',
        DataDefinition::create('string')
          ->setRequired(FALSE)
          ->setSetting('jsonPath', ['messages', 'messageId'])
      )
      ->setPropertyDefinition(
        'body',
        DataDefinition::create('string')
          ->setRequired(FALSE)
          ->setSetting('jsonPath', ['messages', 'body'])
      )
      ->setPropertyDefinition(
        'sentBy',
        DataDefinition::create('string')
          ->setRequired(FALSE)
          ->setSetting('jsonPath', ['messages', 'sentBy'])
      )
      ->setPropertyDefinition(
        'sendDateTime',
        DataDefinition::create('string')
          ->setRequired(FALSE)
          ->setSetting('jsonPath', ['messages', 'sendDateTime'])
      )
      ->setPropertyDefinition(
        'attachments',
        MapDataDefinition::create()
          ->setPropertyDefinition('description',
            DataDefinition::create('string')
              ->setRequired(FALSE)
              ->setSetting('jsonPath', ['description'])
          )
          ->setPropertyDefinition('fileName',
            DataDefinition::create('string')
              ->setRequired(FALSE)
              ->setSetting('jsonPath', ['fileName'])
          )
          ->setRequired(FALSE)
          ->setSetting('jsonPath', ['messages', 'attachments'])
      )
      ->setSetting('jsonPath', ['messages'])
      ->setRequired(FALSE);

    return $info;
  }

}
