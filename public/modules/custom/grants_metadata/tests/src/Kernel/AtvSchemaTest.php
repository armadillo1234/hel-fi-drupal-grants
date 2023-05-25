<?php

namespace Drupal\Tests\grants_metadata\Kernel;

use Symfony\Component\HttpFoundation\Session\Session;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\grants_metadata\AtvSchema;
use Drupal\grants_metadata\TypedData\Definition\KaskoYleisavustusDefinition;
use Drupal\grants_metadata\TypedData\Definition\KuvaProjektiDefinition;
use Drupal\grants_metadata\TypedData\Definition\YleisavustusHakemusDefinition;
use Drupal\webform\Entity\Webform;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests AtvSchema class.
 *
 * @covers DefaultClass \Drupal\grants_metadata\AtvSchema
 * @group grants_metadata
 */
class AtvSchemaTest extends KernelTestBase {
  /**
   * The modules to load to run the test.
   *
   * @var array
   */
  protected static $modules = [
    // Drupal modules.
    'field',
    'user',
    'file',
    'node',
    // Contribs from drupal.org.
    'webform',
    'openid_connect',
    // Contrib hel.fi modules.
    'helfi_audit_log',
    'helfi_helsinki_profiili',
    'helfi_atv',
    'helfi_api_base',
    'helfi_yjdh',
    // Project modules.
    'grants_applicant_info',
    'grants_budget_components',
    'grants_metadata',
    'grants_handler',
    'grants_premises',
    'grants_profile',
    // Test modules.
    'grants_metadata_test_webforms',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Basis for installing webform.
    $this->installSchema('webform', ['webform']);
    // Install test webforms.
    $this->installConfig(['grants_metadata_test_webforms']);
  }

  /**
   * Load webform based on given id.
   */
  public static function loadWebform(string $webformId) {
    return Webform::load($webformId);
  }

  /**
   * Create ATV Schema instance.
   */
  public static function createSchema(): AtvSchema {
    $logger = \Drupal::service('logger.factory');
    $manager = \Drupal::typedDataManager();
    $schema = new AtvSchema($manager, $logger);
    $schema->setSchema('/app/conf/tietoliikennesanoma_schema.json');
    return $schema;
  }

  /**
   * Load test data from data directory.
   */
  public static function loadSubmissionData($formName): array {
    $json = json_decode(file_get_contents(__DIR__ . "/../../data/${formName}.data.json"), TRUE);
    return $json;
  }

  /**
   * Get typed data object for webform data.
   *
   * This is ripped off from ApplicationHandler class.
   *
   * @param array $submittedFormData
   *   Form data.
   * @param string $formId
   *   Webform id.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface
   *   Typed data with values set.
   */
  public static function webformToTypedData(array $submittedFormData, string $formId): TypedDataInterface {

    // Datatype plugin requires the module enablation.
    switch ($formId) {
      case 'yleisavustushakemus':
        $dataDefinition = YleisavustusHakemusDefinition::create('grants_metadata_yleisavustushakemus');
        break;

      case 'kasvatus_ja_koulutus_yleisavustu':
        $dataDefinition = KaskoYleisavustusDefinition::create('grants_metadata_kaskoyleis');
        break;

      case 'kuva_projekti':
        $dataDefinition = KuvaProjektiDefinition::create('grants_metadata_kaskoyleis');
        break;

      default:
        throw new \Exception('Unknown form id');
    }

    $typeManager = $dataDefinition->getTypedDataManager();
    $applicationData = $typeManager->create($dataDefinition);

    $applicationData->setValue($submittedFormData);

    return $applicationData;
  }

  /**
   * Helper function to return web form page structure.
   */
  protected function getPages($webform): array {
    /* If there ends up being different type of page structures this
     * can be extracted from webform data
     */
    $elements = $webform->getElementsDecoded();
    $pageIds = array_keys($elements);
    $pages = [];
    foreach ($pageIds as $pageId) {
      $pages[$pageId] = [
        "#title" => $elements[$pageId]["#title"],
      ];
    }
    return $pages;
  }

  /**
   * Helper function to fetch the given field from document.
   */
  protected function assertDocumentField($document, string $arrayName, int $index, string $fieldName, $fieldValue, $skipMetaChecks = FALSE) {
    $arrayOfFieldData = $document['compensation'][$arrayName][$index];
    $this->assertDocumentFieldArray($arrayOfFieldData, $fieldName, $fieldValue, $skipMetaChecks);
  }

  /**
   * Helper function to fetch the given composite field from document.
   */
  protected function assertDocumentCompositeField($document, string $arrayName, $index, $compositeIndex, string $fieldName, $fieldValue, $skipMetaChecks = FALSE) {
    $arrayOfFieldData = $document['compensation'][$arrayName][$index][$compositeIndex];
    $this->assertDocumentFieldArray($arrayOfFieldData, $fieldName, $fieldValue, $skipMetaChecks);
  }

  /**
   * Helper function to make asserions for a field in document.
   */
  protected function assertDocumentFieldArray($arrayOfFieldData, string $fieldName, $fieldValue, $skipMetaChecks = FALSE) {
    $this->assertArrayHasKey('ID', $arrayOfFieldData);
    $this->assertArrayHasKey('value', $arrayOfFieldData);
    $this->assertArrayHasKey('valueType', $arrayOfFieldData);
    $this->assertArrayHasKey('label', $arrayOfFieldData);
    $this->assertArrayHasKey('meta', $arrayOfFieldData);

    $this->assertEquals($fieldName, $arrayOfFieldData['ID']);
    $this->assertEquals($fieldValue, $arrayOfFieldData['value']);
    if ($skipMetaChecks) {
      return;
    }
    $meta = json_decode($arrayOfFieldData['meta'], TRUE);
    $this->assertArrayHasKey('page', $meta);
    $this->assertArrayHasKey('section', $meta);
    $this->assertArrayHasKey('element', $meta);

  }

  /**
   * @covers \Drupal\grants_metadata\AtvSchema::typedDataToDocumentContentWithWebform
   */
  public function testYleisAvustusHakemus() : void {
    $schema = self::createSchema();
    $webform = self::loadWebform('yleisavustushakemus');
    $this->initSession();
    $this->assertNotNull($webform);
    $pages = self::getPages($webform);
    $submissionData = self::loadSubmissionData('yleisavustushakemus');
    $typedData = self::webformToTypedData($submissionData, 'yleisavustushakemus');
    // Run the actual data conversion.
    $document = $schema->typedDataToDocumentContentWithWebform($typedData, $webform, $pages);
    // Applicant info.
    $this->assertDocumentField($document, 'applicantInfoArray', 0, 'applicantType', '2');
    $this->assertDocumentField($document, 'applicantInfoArray', 1, 'companyNumber', '2036583-2');
    $this->assertDocumentField($document, 'applicantInfoArray', 2, 'registrationDate', '2006-05-10T00:00:00.000+00:00');
    $this->assertDocumentField($document, 'applicantInfoArray', 3, 'foundingYear', '1337');
    $this->assertDocumentField($document, 'applicantInfoArray', 4, 'home', 'VOIKKAA');
    $this->assertDocumentField($document, 'applicantInfoArray', 5, 'homePage', 'arieerola.example.com');
    $this->assertDocumentField($document, 'applicantInfoArray', 6, 'communityOfficialName', 'Maanrakennus Ari Eerola T:mi');
    $this->assertDocumentField($document, 'applicantInfoArray', 7, 'communityOfficialNameShort', 'AE');

    // Applicant officials.
    $this->assertDocumentCompositeField($document, 'applicantOfficialsArray', 0, 0, 'name', 'Ari Eerola');
    $this->assertDocumentCompositeField($document, 'applicantOfficialsArray', 0, 1, 'role', '3');
    $this->assertDocumentCompositeField($document, 'applicantOfficialsArray', 0, 2, 'email', 'ari.eerola@example.com');
    $this->assertDocumentCompositeField($document, 'applicantOfficialsArray', 0, 3, 'phone', '0501234567');
    $this->assertDocumentCompositeField($document, 'applicantOfficialsArray', 1, 0, 'name', 'Eero Arila');
    $this->assertDocumentCompositeField($document, 'applicantOfficialsArray', 1, 1, 'role', '3');
    $this->assertDocumentCompositeField($document, 'applicantOfficialsArray', 1, 2, 'email', 'eero.arila@example.com');
    $this->assertDocumentCompositeField($document, 'applicantOfficialsArray', 1, 3, 'phone', '0507654321');
    // Contact Info and Address.
    $this->assertDocumentField($document, 'currentAddressInfoArray', 0, 'contactPerson', 'Eero Arila');
    $this->assertDocumentField($document, 'currentAddressInfoArray', 1, 'phoneNumber', '0507654321');
    $this->assertDocumentField($document, 'currentAddressInfoArray', 2, 'street', 'Testitie 1');
    $this->assertDocumentField($document, 'currentAddressInfoArray', 3, 'city', 'Testilä');
    $this->assertDocumentField($document, 'currentAddressInfoArray', 4, 'postCode', '00100');
    $this->assertDocumentField($document, 'currentAddressInfoArray', 5, 'country', 'Suomi');
    // Application Info.
    $this->assertDocumentField($document, 'applicationInfoArray', 0, 'applicationNumber', 'GRANTS-LOCALPAK-ECONOMICGRANTAPPLICATION-00000001');
    $this->assertDocumentField($document, 'applicationInfoArray', 1, 'status', 'DRAFT');
    $this->assertDocumentField($document, 'applicationInfoArray', 2, 'actingYear', '2023');
    // compensationInfo.
    $this->assertDocumentCompositeField($document, 'compensationInfo', 'generalInfoArray', 0, 'compensationPreviousYear', '');
    $this->assertDocumentCompositeField($document, 'compensationInfo', 'generalInfoArray', 1, 'totalAmount', '0', TRUE);
    // Handle subventions.
    $arrayOfFieldData = $document['compensation']['compensationInfo']['compensationArray'][0][0];
    $this->assertDocumentFieldArray($arrayOfFieldData, 'subventionType', '1');
    $arrayOfFieldData = $document['compensation']['compensationInfo']['compensationArray'][0][1];
    $this->assertDocumentFieldArray($arrayOfFieldData, 'amount', '0');
    $arrayOfFieldData = $document['compensation']['compensationInfo']['compensationArray'][1][0];
    $this->assertDocumentFieldArray($arrayOfFieldData, 'subventionType', '5');
    $arrayOfFieldData = $document['compensation']['compensationInfo']['compensationArray'][1][1];
    $this->assertDocumentFieldArray($arrayOfFieldData, 'amount', '0');
    $arrayOfFieldData = $document['compensation']['compensationInfo']['compensationArray'][2][0];
    $this->assertDocumentFieldArray($arrayOfFieldData, 'subventionType', '36');
    $arrayOfFieldData = $document['compensation']['compensationInfo']['compensationArray'][2][1];
    $this->assertDocumentFieldArray($arrayOfFieldData, 'amount', '0');

    // bankAccountArray.
    $this->assertDocumentField($document, 'bankAccountArray', 0, 'accountNumber', 'FI21 1234 5600 0007 85');

    // benefitsInfoArray.
    $this->assertDocumentField($document, 'benefitsInfoArray', 0, 'loans', '13');
    $this->assertDocumentField($document, 'benefitsInfoArray', 1, 'premises', '13');
    // activitiesInfoArray.
    $this->assertDocumentField($document, 'activitiesInfoArray', 0, 'businessPurpose', 'Massin teko');
    $this->assertDocumentField($document, 'activitiesInfoArray', 1, 'membersApplicantPersonLocal', '100');
    $this->assertDocumentField($document, 'activitiesInfoArray', 2, 'membersApplicantPersonGlobal', '150');
    $this->assertDocumentField($document, 'activitiesInfoArray', 3, 'membersApplicantCommunityLocal', '10');
    $this->assertDocumentField($document, 'activitiesInfoArray', 4, 'membersApplicantCommunityGlobal', '15');
    $this->assertDocumentField($document, 'activitiesInfoArray', 5, 'feePerson', '10');
    $this->assertDocumentField($document, 'activitiesInfoArray', 6, 'feeCommunity', '200');
  }

  /**
   * Create session for GrantsProfileService.
   */
  protected function initSession(): void {
    $session = new Session();
    \Drupal::service('grants_profile.service')->setSession($session);
    \Drupal::service('grants_profile.service')->setApplicantType('registered_community');
  }

  /**
   * @covers \Drupal\grants_metadata\AtvSchema::typedDataToDocumentContentWithWebform
   */
  public function testKaskoYleisAvustusHakemus() : void {
    $schema = self::createSchema();
    $webform = self::loadWebform('kasvatus_ja_koulutus_yleisavustu');
    $pages = self::getPages($webform);
    $this->assertNotNull($webform);
    $this->initSession();
    $submissionData = self::loadSubmissionData('kasvatus_ja_koulutus_yleisavustu');
    $typedData = self::webformToTypedData($submissionData, 'kasvatus_ja_koulutus_yleisavustu');
    // Run the actual data conversion.
    $document = $schema->typedDataToDocumentContentWithWebform($typedData, $webform, $pages);
    // Applicant info.
    $this->assertDocumentField($document, 'applicantInfoArray', 0, 'applicantType', '2');
    $this->assertDocumentField($document, 'applicantInfoArray', 1, 'companyNumber', '2036583-2');
    $this->assertDocumentField($document, 'applicantInfoArray', 2, 'registrationDate', '2006-05-10T00:00:00.000+00:00');
    $this->assertDocumentField($document, 'applicantInfoArray', 3, 'foundingYear', '1337');
    $this->assertDocumentField($document, 'applicantInfoArray', 4, 'home', 'VOIKKAA');
    $this->assertDocumentField($document, 'applicantInfoArray', 5, 'homePage', 'arieerola.example.com');
    $this->assertDocumentField($document, 'applicantInfoArray', 6, 'communityOfficialName', 'Maanrakennus Ari Eerola T:mi');
    $this->assertDocumentField($document, 'applicantInfoArray', 7, 'communityOfficialNameShort', 'AE');

    $this->assertDocumentField($document, 'applicantInfoArray', 8, 'email', 'ari.eerola@example.com');

    // Applicant officials.
    $this->assertDocumentCompositeField($document, 'applicantOfficialsArray', 0, 0, 'name', 'Ari Eerola');
    $this->assertDocumentCompositeField($document, 'applicantOfficialsArray', 0, 1, 'role', '3');
    $this->assertDocumentCompositeField($document, 'applicantOfficialsArray', 0, 2, 'email', 'ari.eerola@example.com');
    $this->assertDocumentCompositeField($document, 'applicantOfficialsArray', 0, 3, 'phone', '0501234567');
    $this->assertDocumentCompositeField($document, 'applicantOfficialsArray', 1, 0, 'name', 'Eero Arila');
    $this->assertDocumentCompositeField($document, 'applicantOfficialsArray', 1, 1, 'role', '3');
    $this->assertDocumentCompositeField($document, 'applicantOfficialsArray', 1, 2, 'email', 'eero.arila@example.com');
    $this->assertDocumentCompositeField($document, 'applicantOfficialsArray', 1, 3, 'phone', '0507654321');
    // Contact Info and Address.
    $this->assertDocumentField($document, 'currentAddressInfoArray', 0, 'contactPerson', 'Eero Arila');
    $this->assertDocumentField($document, 'currentAddressInfoArray', 1, 'phoneNumber', '0507654321');
    $this->assertDocumentField($document, 'currentAddressInfoArray', 2, 'street', 'Testitie 1');
    $this->assertDocumentField($document, 'currentAddressInfoArray', 3, 'city', 'Testilä');
    $this->assertDocumentField($document, 'currentAddressInfoArray', 4, 'postCode', '00100');
    $this->assertDocumentField($document, 'currentAddressInfoArray', 5, 'country', 'Suomi');
    // Application Info.
    $this->assertDocumentField($document, 'applicationInfoArray', 0, 'applicationNumber', 'GRANTS-LOCALPAK-KASKOYLEIS-00000001');
    $this->assertDocumentField($document, 'applicationInfoArray', 1, 'status', 'DRAFT');
    $this->assertDocumentField($document, 'applicationInfoArray', 2, 'actingYear', '2023');
    // compensationInfo.
    $this->assertDocumentCompositeField($document, 'compensationInfo', 'generalInfoArray', 0, 'compensationPreviousYear', '');
    $this->assertDocumentCompositeField($document, 'compensationInfo', 'generalInfoArray', 1, 'totalAmount', '0', TRUE);
    // Handle subventions.
    $arrayOfFieldData = $document['compensation']['compensationInfo']['compensationArray'][0][0];
    $this->assertDocumentFieldArray($arrayOfFieldData, 'subventionType', '1');
    $arrayOfFieldData = $document['compensation']['compensationInfo']['compensationArray'][0][1];
    $this->assertDocumentFieldArray($arrayOfFieldData, 'amount', '0');
    $arrayOfFieldData = $document['compensation']['compensationInfo']['compensationArray'][1][0];
    $this->assertDocumentFieldArray($arrayOfFieldData, 'subventionType', '5');
    $arrayOfFieldData = $document['compensation']['compensationInfo']['compensationArray'][1][1];
    $this->assertDocumentFieldArray($arrayOfFieldData, 'amount', '0');
    $arrayOfFieldData = $document['compensation']['compensationInfo']['compensationArray'][2][0];
    $this->assertDocumentFieldArray($arrayOfFieldData, 'subventionType', '36');
    $arrayOfFieldData = $document['compensation']['compensationInfo']['compensationArray'][2][1];
    $this->assertDocumentFieldArray($arrayOfFieldData, 'amount', '0');

    // bankAccountArray.
    $this->assertDocumentField($document, 'bankAccountArray', 0, 'accountNumber', 'FI21 1234 5600 0007 85');

    // benefitsInfoArray.
    $this->assertDocumentField($document, 'benefitsInfoArray', 0, 'loans', '13');
    $this->assertDocumentField($document, 'benefitsInfoArray', 1, 'premises', '13');
    // activitiesInfoArray.
    $this->assertDocumentField($document, 'activitiesInfoArray', 0, 'businessPurpose', 'Massin teko');
    $this->assertDocumentField($document, 'activitiesInfoArray', 1, 'membersApplicantPersonLocal', '100');
    $this->assertDocumentField($document, 'activitiesInfoArray', 2, 'membersApplicantPersonGlobal', '150');
    $this->assertDocumentField($document, 'activitiesInfoArray', 3, 'membersApplicantCommunityLocal', '10');
    $this->assertDocumentField($document, 'activitiesInfoArray', 4, 'membersApplicantCommunityGlobal', '15');
    $this->assertDocumentField($document, 'activitiesInfoArray', 5, 'feePerson', '10');
    $this->assertDocumentField($document, 'activitiesInfoArray', 6, 'feeCommunity', '200');
  }

  /**
   * @covers \Drupal\grants_metadata\AtvSchema::typedDataToDocumentContentWithWebform
   */
  public function testKuvaProjektiHakemus() : void {
    $schema = self::createSchema();
    $webform = self::loadWebform('kuva_projekti');
    $pages = self::getPages($webform);
    $this->assertNotNull($webform);
    $this->initSession();
    $submissionData = self::loadSubmissionData('kuva_projekti');
    $typedData = self::webformToTypedData($submissionData, 'kuva_projekti');
    // Run the actual data conversion.
    $document = $schema->typedDataToDocumentContentWithWebform($typedData, $webform, $pages);
    $this->assertDocumentField($document, 'applicantInfoArray', 0, 'applicantType', '2');
    $this->assertDocumentField($document, 'applicantInfoArray', 1, 'companyNumber', '2036583-2');
    $this->assertDocumentField($document, 'applicantInfoArray', 2, 'registrationDate', '2006-05-10T00:00:00.000+00:00');
    $this->assertDocumentField($document, 'applicantInfoArray', 3, 'foundingYear', '1345');
    $this->assertDocumentField($document, 'applicantInfoArray', 4, 'home', 'VOIKKAA');
    $this->assertDocumentField($document, 'applicantInfoArray', 5, 'homePage', 'arieerola.example.com');
    $this->assertDocumentField($document, 'applicantInfoArray', 6, 'communityOfficialName', 'Maanrakennus Ari Eerola T:mi');
    $this->assertDocumentField($document, 'applicantInfoArray', 7, 'communityOfficialNameShort', 'AE');
  }

}
