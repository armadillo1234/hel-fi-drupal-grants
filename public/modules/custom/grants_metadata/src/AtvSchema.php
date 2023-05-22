<?php

namespace Drupal\grants_metadata;

use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Component\Serialization\Json;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\grants_attachments\AttachmentHandler;
use Drupal\grants_attachments\Plugin\WebformElement\GrantsAttachments;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;

/**
 * Map ATV documents to typed data.
 */
class AtvSchema {

  use StringTranslationTrait;
  /**
   * Drupal\Core\TypedData\TypedDataManager definition.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected TypedDataManager $typedDataManager;

  /**
   * Schema structure as parsed from schema file.
   *
   * @var array
   */
  protected array $structure;

  /**
   * Path to schema file.
   *
   * @var string
   */
  protected string $atvSchemaPath;

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected LoggerChannel $logger;

  /**
   * Constructs an AtvShcema object.
   */
  public function __construct(TypedDataManager $typed_data_manager, LoggerChannelFactory $loggerFactory) {

    $this->typedDataManager = $typed_data_manager;
    $this->logger = $loggerFactory->get('grants_attachments');

  }

  /**
   * Load json schema file.
   *
   * @param string $schemaPath
   *   Path to schema file.
   */
  public function setSchema(string $schemaPath) {

    if ($schemaPath != '') {
      $jsonString = file_get_contents($schemaPath);
      $jsonStructure = Json::decode($jsonString);

      $this->structure = $jsonStructure;
    }

  }

  /**
   * Map document structure to typed data object.
   *
   * @param array $documentData
   *   Document as array.
   * @param \Drupal\Core\TypedData\ComplexDataDefinitionInterface $typedDataDefinition
   *   Data definition for this document / application.
   * @param array|null $metadata
   *   Metadata to attach.
   *
   * @return array
   *   Mapped dta from document.
   */
  public function documentContentToTypedData(
    array $documentData,
    ComplexDataDefinitionInterface $typedDataDefinition,
    ?array $metadata = []
  ): array {
    if (isset($documentData['content']) && is_array($documentData['content'])) {
      $documentContent = $documentData['content'];
    }
    else {
      $documentContent = $documentData;
    }

    $propertyDefinitions = $typedDataDefinition->getPropertyDefinitions();

    $typedDataValues = [];

    foreach ($propertyDefinitions as $definitionKey => $definition) {
      $jsonPath = $definition->getSetting('jsonPath');
      $webformDataExtractor = $definition->getSetting('webformDataExtracter');

      if ($webformDataExtractor) {
        $arguments = $webformDataExtractor['arguments'] ?? [];
        $extractedValues = self::getWebformDataFromContent($webformDataExtractor, $documentData, $definition, $arguments);
        if (isset($webformDataExtractor['mergeResults']) && $webformDataExtractor['mergeResults']) {
          $typedDataValues = array_merge($typedDataValues, $extractedValues);
        }
        else {
          $typedDataValues[$definitionKey] = $extractedValues;
        }
      }
      else {
        // If json path not configured for item, do nothing.
        if (is_array($jsonPath)) {
          $elementName = array_pop($jsonPath);

          $typedDataValues[$definitionKey] = $this->getValueFromDocument($documentContent, $jsonPath, $elementName, $definition);
        }
      }
    }
    if (isset($typedDataValues['status_updates']) && is_array($typedDataValues['status_updates'])) {
      // Loop status updates & set the last one as submission status.
      foreach ($typedDataValues['status_updates'] as $status) {
        $typedDataValues['status'] = $status['citizenCaseStatus'];
      }
    }

    $other_attachments = [];
    $attachmentFileTypes = AttachmentHandler::getAttachmentFieldNames($typedDataValues["application_number"], TRUE);
    $attachmentHeaders = GrantsAttachments::$fileTypes;

    if (!isset($typedDataValues["attachments"])) {
      $typedDataValues["attachments"] = [];
    }

    foreach ($typedDataValues["attachments"] as $key => $attachment) {
      $headerKey = array_search($attachment["description"], $attachmentHeaders);
      $thisHeader = $attachmentHeaders[$headerKey];
      $fieldName = array_search($headerKey, $attachmentFileTypes);

      $newValues = $attachment;

      // If we have fileName property we know the file is definitely not new.
      if (isset($attachment["fileName"]) && $attachment["fileName"] !== '') {
        $newValues["isNewAttachment"] = 'false';
        $newValues['attachmentName'] = $attachment['fileName'];
      }

      // @todo Do away with hard coded field name for muu liite.
      if ($fieldName === 'muu_liite') {
        $other_attachments[$key] = $newValues;
        unset($typedDataValues["attachments"][$key]);
      }
      else {
        if ($newValues['description'] === $thisHeader) {
          $typedDataValues[$fieldName] = $newValues;
        }
      }
    }

    $community_address = [];
    if (isset($typedDataValues['community_street'])) {
      $community_address['community_street'] = $typedDataValues['community_street'];
      unset($typedDataValues['community_street']);
    }
    if (isset($typedDataValues['community_city'])) {
      $community_address['community_city'] = $typedDataValues['community_city'];
      unset($typedDataValues['community_city']);
    }
    if (isset($typedDataValues['community_post_code'])) {
      $community_address['community_post_code'] = $typedDataValues['community_post_code'];
      unset($typedDataValues['community_post_code']);
    }
    if (isset($typedDataValues['community_country'])) {
      $community_address['community_country'] = $typedDataValues['community_country'];
      unset($typedDataValues['community_country']);
    }

    $typedDataValues['community_address'] = $community_address;

    if (isset($typedDataValues['account_number'])) {
      $typedDataValues['bank_account']['account_number'] = $typedDataValues['account_number'];
      $typedDataValues['bank_account']['account_number_select'] = $typedDataValues['account_number'];
    }

    if (isset($typedDataValues['community_practices_business'])) {
      if ($typedDataValues['community_practices_business'] === 'false') {
        $typedDataValues['community_practices_business'] = 0;
      }
      if ($typedDataValues['community_practices_business'] === 'true') {
        $typedDataValues['community_practices_business'] = 1;
      }

    }

    $typedDataValues['muu_liite'] = $other_attachments;

    $typedDataValues['metadata'] = $metadata;
    return $typedDataValues;

  }

  /**
   * Get schema definition for single property.
   *
   * @param string $elementName
   *   Name of the element.
   * @param array $structure
   *   Full schema structure.
   *
   * @return mixed
   *   Schema for given property.
   */
  protected function getPropertySchema(string $elementName, array $structure): mixed {

    foreach ($structure['properties'] as $topLevelElement) {
      if ($topLevelElement['type'] == 'object') {
        if (array_key_exists($elementName, $topLevelElement['properties'])) {
          return $topLevelElement['properties'][$elementName];
        }
        else {
          foreach ($topLevelElement['properties'] as $element0) {
            if ($element0['type'] == 'array') {
              if ($element0['items']['type'] == 'object') {
                if (in_array($elementName, $element0['items']['properties']['ID']['enum'])) {
                  return $element0['items'];
                }
              }
              else {
                if (in_array($elementName, $element0['items']['items']['properties']['ID']['enum'])) {
                  return $element0['items']['items'];
                }
              }
            }
            if ($element0['type'] == 'object') {
              if (array_key_exists($elementName, $element0['properties'])) {
                return $element0['properties'][$elementName];
              }
              else {
                foreach ($element0['properties'] as $element1) {
                  if ($element1['type'] == 'array') {
                    if ($element1['items']['type'] == 'object') {
                      if (isset($element1['items']['properties']['ID']) && array_key_exists('enum', $element1['items']['properties']['ID'])) {
                        if (is_array($element1['items']['properties']['ID']['enum']) && in_array($elementName, $element1['items']['properties']['ID']['enum'])) {
                          return $element1['items'];
                        }
                      }
                    }
                    else {
                      if (in_array($elementName, $element1['items']['items']['properties']['ID']['enum'])) {
                        return $element1['items']['items'];
                      }
                    }
                  }
                }
              }
            }
            if ($element0['type'] == 'string') {
              return $element0;
            }
          }
        }
      }
    }
    return NULL;
  }

  /**
   * PArse accepted json datatype & actual datatype from definitions.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $definition
   *   Data definition for item.
   *
   * @return string[]
   *   Array with dataType & jsonType.
   */
  public static function getJsonTypeForDataType(DataDefinitionInterface $definition): array {
    $propertyType = $definition->getDataType();
    // Default both types same.
    $retval = [
      'dataType' => $propertyType,
      'jsonType' => $propertyType,
    ];
    // If override, then override given value.
    if ($typeOverride = $definition->getSetting('typeOverride')) {
      if (isset($typeOverride['dataType'])) {
        $retval['dataType'] = $typeOverride['dataType'];
      }
      if (isset($typeOverride['jsonType'])) {
        $retval['jsonType'] = $typeOverride['jsonType'];
      }
    }
    if ($propertyType == 'integer') {
      $retval['jsonType'] = 'int';
    }
    elseif ($propertyType == 'datetime_iso8601') {
      $retval['jsonType'] = 'datetime';
    }
    elseif ($propertyType == 'boolean') {
      $retval['jsonType'] = 'bool';
    }

    return $retval;
  }

  /**
   * Sanitize input to make sure there's no illegal input.
   *
   * @param mixed $value
   *   Value to be sanitized.
   *
   * @return mixed
   *   Sanitized value.
   */
  public static function sanitizeInput(mixed $value): mixed {

    if (is_array($value)) {
      array_walk_recursive($value, function (&$item) {
        if (is_string($item)) {
          $item = filter_var($item, FILTER_UNSAFE_RAW);
        }
      });
    }
    else {
      $value = filter_var($value, FILTER_UNSAFE_RAW);
    }

    return $value;
  }

  /**
   * Generate document content JSON from typed data.
   *
   * @param \Drupal\Core\TypedData\TypedDataInterface $typedData
   *   Typed data to export.
   * @param \Drupal\webform\Entity\WebformSubmission $webformSubmission
   *   Form submission entity.
   *
   * @return array
   *   Document structure based on schema.
   */
  public function typedDataToDocumentContent(
    TypedDataInterface $typedData,
    WebformSubmission $webformSubmission): array {

    $webform = $webformSubmission->getWebform();
    $pages = $webform->getPages('edit', $webformSubmission);
    return $this->typedDataToDocumentContentWithWebform($typedData, $webform, $pages);
  }

  /**
   * Generate document content JSON from typed data using submission.
   *
   * @param \Drupal\Core\TypedData\TypedDataInterface $typedData
   *   Typed data to export.
   * @param \Drupal\webform\Entity\Webform $webform
   *   Form entity.
   * @param array $pages
   *   Page structure of webform.
   *
   * @return array
   *   Document structure based on schema.
   */
  public function typedDataToDocumentContentWithWebform(
    TypedDataInterface $typedData,
    Webform $webform,
    array $pages): array {

    $pageKeys = array_keys($pages);
    $elements = $webform->getElementsDecodedAndFlattened();
    $elementKeys = array_keys($elements);
    $documentStructure = [];
    $addedElements = [];
    foreach ($typedData as $property) {
      $definition = $property->getDataDefinition();

      $jsonPath = $definition->getSetting('jsonPath');
      $requiredInJson = $definition->getSetting('requiredInJson');
      $defaultValue = $definition->getSetting('defaultValue');
      // What to do with empty values.
      $itemSkipEmpty = $definition->getSetting('skipEmptyValue');

      $valueCallback = $definition->getSetting('valueCallback');
      $fullItemValueCallback = $definition->getSetting('fullItemValueCallback');

      $propertyStructureCallback = $definition->getSetting('propertyStructureCallback');

      // Get property name.
      $propertyName = $property->getName();

      /* Try to get element from webform. This tells usif we can try to get
      metadata from webform. If not, field is not printable. */
      $webformElement = $webform->getElement($propertyName);

      $isRegularField = $propertyName !== 'form_update' &&
        $propertyName !== 'messages' &&
        $propertyName !== 'status_updates' &&
        $propertyName !== 'events' &&
        $webformElement !== NULL;

      if ($jsonPath == NULL && $isRegularField) {
        continue;
      }
      if ($propertyName == 'account_number') {
        $propertyName = 'bank_account';
      }

      /* Regular field and one that has webform element & can be used with
      metadata & can hence be printed out. No webform, no printing of
      the element. */
      if ($isRegularField) {
        if ($propertyName == 'community_street' || $propertyName == 'community_city' || $propertyName == 'community_post_code' || $propertyName == 'community_country') {
          $webformMainElement = $webform->getElement('community_address');
          $webformLabelElement = $webformMainElement['#webform_composite_elements'][$propertyName];
          $propertyName = 'community_address';
        }
        else {
          $webformMainElement = $webformElement;
          $webformLabelElement = $webformElement;
        }

        // If we have structure callback defined, then get property structure.
        if ($propertyStructureCallback) {
          $structureArray = self::getFieldValuesFromFullItemCallback(
          $propertyStructureCallback,
          $property,
          $definition
          );
          $elementWeight = 0;
          foreach ($structureArray["compensation"] as $propertyArrayKey => $propertyArray) {
            foreach ($propertyArray as $propertyKey => $property) {
              if (!isset($property['ID'])) {
                continue;
              }
              $name = $property['ID'];
              $pageId = $webformMainElement['#webform_parents'][0];
              $pageLabel = $pages[$pageId]['#title'];
              $pageNumber = array_search($pageId, $pageKeys) + 1;
              // Then section.
              $sectionId = $webformMainElement['#webform_parents'][1];
              $sectionLabel = $elements[$sectionId]['#title'];
              $sectionWeight = array_search($sectionId, $elementKeys);
              // Finally the element itself.
              $label = $property['label'];
              $weight = array_search($name, $elementKeys);
              $page = [
                'id' => $pageId,
                'label' => $pageLabel,
                'number' => $pageNumber,
              ];
              $section = [
                'id' => $sectionId,
                'label' => $sectionLabel,
                'weight' => $sectionWeight,
              ];
              $element = [
                'label' => $label,
                'weight' => $elementWeight,
              ];
              $elementWeight++;
              $metaData = self::getMetaData($page, $section, $element);
              $structureArray["compensation"][$propertyArrayKey][$propertyKey]['meta'] = json_encode($metaData);
            }
          }
          $documentStructure = array_merge_recursive(
            $documentStructure,
            $structureArray
          );
          continue;
        }

        if ($propertyName !== 'attachments') {
          // Dig up the data from webform. First page.
          $pageId = $webformMainElement['#webform_parents'][0];

          $pageLabel = $pages[$pageId]['#title'];
          $pageNumber = array_search($pageId, $pageKeys) + 1;
          // Then section.
          $sectionId = $webformMainElement['#webform_parents'][1];
          $sectionLabel = $elements[$sectionId]['#title'];
          $sectionWeight = array_search($sectionId, $elementKeys);
          // Finally the element itself.
          $label = $webformLabelElement['#title'];
          $weight = array_search($propertyName, $elementKeys);
        }
        else {
          // Attachments are very very custom field.
          $pageId = 'lisatiedot_ja_liitteet';
          $pageLabel = $pages[$pageId]['#title'];
          $pageNumber = array_search($pageId, $pageKeys) + 1;
          // Then section.
          $sectionId = 'lisatiedot_ja_liitteet_section';
          $sectionLabel = $this->t('Attachments');
          $sectionWeight = 0;
          // Finally the element itself.
          $label = $this->t('Attachments');
          $weight = array_search($propertyName, $elementKeys);
        }

        $page = [
          'id' => $pageId,
          'label' => $pageLabel,
          'number' => $pageNumber,
        ];
        $section = [
          'id' => $sectionId,
          'label' => $sectionLabel,
          'weight' => $sectionWeight,
        ];
        $element = [
          'label' => $label,
          'weight' => $weight,
        ];
        $metaData = self::getMetaData($page, $section, $element);
      }
      else {

        if ($propertyStructureCallback) {

          $addWebformToCallback = $propertyStructureCallback['webform'] ?? FALSE;
          if ($addWebformToCallback) {
            $propertyStructureCallback['arguments']['webform'] = $webform;
          }

          $documentStructure = array_merge_recursive(
            $documentStructure,
            self::getFieldValuesFromFullItemCallback(
              $propertyStructureCallback,
              $property,
              $definition
            )
          );
          continue;
        }

        $label = $definition->getLabel();
        $metaData = [];
      }
      $propertyType = $definition->getDataType();

      $numberOfItems = count($jsonPath);
      $elementName = array_pop($jsonPath);
      $baseIndex = count($jsonPath);

      if (!isset($addedElements[$numberOfItems])) {
        $addedElements[$numberOfItems] = [];
      }

      $value = self::sanitizeInput($property->getValue());

      $schema = $this->getPropertySchema($elementName, $this->structure);

      $itemTypes = self::getJsonTypeForDataType($definition);
      $itemValue = self::getItemValue($itemTypes, $value, $defaultValue, $valueCallback);

      if ($propertyType == 'integer' ||
        $propertyType == 'double' ||
        $propertyType == 'float') {

        // Leave zero values out of json.
        if ($itemValue === '0' && $defaultValue === NULL) {
          continue;
        }
      }
      else {
        // Also remove other empty valued fields.
        if ($itemValue === '' && $defaultValue === NULL) {
          continue;
        }
      }

      switch ($numberOfItems) {
        case 4:
          $valueArray = [
            'ID' => $elementName,
            'value' => $itemValue,
            'valueType' => $itemTypes['jsonType'],
            'label' => $label,
            'meta' => json_encode($metaData),
          ];
          $documentStructure[$jsonPath[0]][$jsonPath[1]][$jsonPath[2]][] = $valueArray;
          $addedElements[$numberOfItems][] = $elementName;
          break;

        case 3:

          if (is_array($itemValue) && self::numericKeys($itemValue)) {
            if ($fullItemValueCallback) {
              $fieldValues = self::getFieldValuesFromFullItemCallback($fullItemValueCallback, $property);
              if (empty($fieldValues)) {
                if ($requiredInJson) {
                  $documentStructure[$jsonPath[0]][$jsonPath[1]][$elementName] = $fieldValues;
                }
              }
              else {
                $documentStructure[$jsonPath[0]][$jsonPath[1]][$elementName] = $fieldValues;
              }
            }
            else {
              if (empty($itemValue)) {
                if ($requiredInJson) {
                  $documentStructure[$jsonPath[0]][$jsonPath[1]][$elementName] = $itemValue;
                }
              }
              else {
                foreach ($property as $itemIndex => $item) {
                  $fieldValues = [];
                  $propertyItem = $item->getValue();
                  $itemDataDefinition = $item->getDataDefinition();
                  $itemValueDefinitions = $itemDataDefinition->getPropertyDefinitions();
                  foreach ($itemValueDefinitions as $itemName => $itemValueDefinition) {
                    $itemTypes = $this->getJsonTypeForDataType($itemValueDefinition);
                    // Backup label.
                    $label = $itemValueDefinition->getLabel();
                    if (isset($webformMainElement['#webform_composite_elements'][$itemName]['#title'])) {
                      $titleElement = $webformMainElement['#webform_composite_elements'][$itemName]['#title'];
                      if (is_string($titleElement)) {
                        $label = $titleElement;
                      }
                      else {
                        $label = $titleElement->render();
                      }
                    }
                    $element = [
                      'weight' => $weight,
                      'label' => $label,
                    ];

                    if (isset($propertyItem[$itemName])) {
                      $itemValue = $propertyItem[$itemName];

                      $itemValue = $this->getItemValue($itemTypes, $itemValue, $defaultValue, $valueCallback);

                      $idValue = $itemName;
                      $metaData = self::getMetaData($page, $section, $element);
                      $valueArray = [
                        'ID' => $idValue,
                        'value' => $itemValue,
                        'valueType' => $itemTypes['jsonType'],
                        'label' => $label,
                        'meta' => json_encode($metaData),
                      ];
                      $fieldValues[] = $valueArray;
                    }
                  }
                  $documentStructure[$jsonPath[0]][$jsonPath[1]][$elementName][$itemIndex] = $fieldValues;
                  $addedElements[$numberOfItems][] = $elementName;
                }
              }
            }
          }
          else {
            $valueArray = [
              'ID' => $elementName,
              'value' => $itemValue,
              'valueType' => $itemTypes['jsonType'],
              'label' => $label,
              'meta' => json_encode($metaData),
            ];
            if ($schema['type'] == 'number') {
              if ($itemValue == NULL) {
                if ($requiredInJson) {
                  $documentStructure[$jsonPath[0]][$jsonPath[1]][] = $valueArray;
                }
              }
              else {
                $documentStructure[$jsonPath[0]][$jsonPath[1]][] = $valueArray;
              }
            }
            else {
              $documentStructure[$jsonPath[0]][$jsonPath[1]][] = $valueArray;
            }
          }
          break;

        case 2:
          if (
            is_array($value) &&
            self::numericKeys($value)) {
            if ($propertyType == 'list') {
              foreach ($property as $itemIndex => $item) {
                $fieldValues = [];
                $propertyItem = $item->getValue();
                $itemDataDefinition = $item->getDataDefinition();
                $itemValueDefinitions = $itemDataDefinition->getPropertyDefinitions();
                foreach ($itemValueDefinitions as $itemName => $itemValueDefinition) {
                  // Backup label.
                  $label = $itemValueDefinition->getLabel();
                  if (isset($webformMainElement['#webform_composite_elements'][$itemName]['#title'])) {
                    $label = $webformMainElement['#webform_composite_elements'][$itemName]['#title']->render();
                  }
                  $element = [
                    'weight' => $weight,
                    'label' => $label,
                  ];
                  $itemTypes = self::getJsonTypeForDataType($itemValueDefinition);
                  if (isset($propertyItem[$itemName])) {
                    // What to do with empty values.
                    $itemSkipEmpty = $itemValueDefinition->getSetting('skipEmptyValue');

                    $itemValue = $propertyItem[$itemName];
                    $itemValue = self::getItemValue($itemTypes, $itemValue, $defaultValue, $valueCallback);
                    // If no value and skip is setting, then skip.
                    if (empty($itemValue) && $itemSkipEmpty === TRUE) {
                      continue;
                    }
                    $metaData = self::getMetaData($page, $section, $element);

                    $idValue = $itemName;
                    $valueArray = [
                      'ID' => $idValue,
                      'value' => $itemValue,
                      'valueType' => $itemTypes['jsonType'],
                      'label' => $label,
                      'meta' => json_encode($metaData),
                    ];
                    $fieldValues[] = $valueArray;
                  }
                }
                $documentStructure[$jsonPath[0]][$elementName][$itemIndex] = $fieldValues;
              }
            }
          }
          else {
            $valueArray = [
              'ID' => $elementName,
              'value' => $itemValue,
              'valueType' => $itemTypes['jsonType'],
              'label' => $label,
              'meta' => json_encode($metaData),
            ];
            if ($schema['type'] == 'string') {
              $documentStructure[$jsonPath[$baseIndex - 1]][$elementName] = $itemValue;
            }
            else {
              $documentStructure[$jsonPath[$baseIndex - 1]][] = $valueArray;
            }
          }

          break;

        case 1:
          if ($propertyName == 'form_update') {
            if ($itemValue === 'true') {
              $documentStructure[$elementName] = TRUE;
            }
            else {
              $documentStructure[$elementName] = FALSE;
            }
          }
          else {
            $documentStructure[$elementName] = $itemValue;
          }
          break;

        default:
          $this->logger->error('@field failed parsing, check setup.', ['@field' => $elementName]);
          break;
      }
    }
    if (!array_key_exists('attachmentsInfo', $documentStructure)) {
      $documentStructure['attachmentsInfo'] = [];
    }
    return $documentStructure;
  }

  /**
   * Get metadata array for JSON schema meta field.
   *
   * This function is used to guarantee that meta field in
   * schema will always have necessary fields.
   *
   * @param array|null $page
   *   Array with page data.
   * @param array|null $section
   *   Array with section data.
   * @param array|null $element
   *   Array with element data.
   *
   * @return array
   *   MetaData array
   */
  public static function getMetaData(?array $page = [], ?array $section = [], ?array $element = []): array {
    $metaData = [
      'page' => [
        'id' => $page['id'] ?? 'unknown_page',
        'number' => $page['number'] ?? -1,
        'label' => $page['label'] ?? 'Page',
      ],
      'section' => [
        'id' => $section['id'] ?? 'unknown_section',
        'weight' => $section['weight'] ?? -1,
        'label' => $section['label'] ?? 'Section',
      ],
      'element' => [
        'weight' => $element['weight'] ?? -1,
        'label' => $element['label'] ?? 'Element',
      ],
    ];
    return $metaData;

  }

  /**
   * Look for numeric keys in array, and return if they're found or not.
   *
   * @param array $array
   *   Array to look in.
   *
   * @return bool
   *   Is there only numeric keys?
   */
  public static function numericKeys(array $array): bool {
    $non_numeric_key_found = FALSE;

    foreach (array_keys($array) as $key) {
      if (!is_numeric($key)) {
        $non_numeric_key_found = TRUE;
      }
    }
    return !$non_numeric_key_found;
  }

  /**
   * Get value from document content for given element / path.
   *
   * @param array $content
   *   Decoded JSON content for document.
   * @param array $pathArray
   *   Path in JSONn document. From definition settings.
   * @param string $elementName
   *   ELement name in JSON.
   * @param \Drupal\Core\TypedData\DataDefinitionInterface|null $definition
   *   Data definition setup.
   *
   * @return mixed
   *   Parsed typed data structure.
   */
  protected function getValueFromDocument(
    array $content,
    array $pathArray,
    string $elementName,
    ?DataDefinitionInterface $definition
  ): mixed {
    // Get new key to me evalued.
    $newKey = array_shift($pathArray);

    $type = $definition->getDataType();

    // If key exist in content array.
    if (array_key_exists($newKey, $content)) {
      // Get content for key.
      $newContent = $content[$newKey];
      // And since we're not in root element, call self
      // to drill down to desired element.
      return $this->getValueFromDocument($newContent, $pathArray, $elementName, $definition);
    }
    // If we are at the root of content, and the given element exists.
    elseif (array_key_exists($elementName, $content)) {

      $thisElement = $content[$elementName];

      // If element is array.
      if (is_array($thisElement)) {
        $retval = [];
        // We need to loop values and structure data in array as well.
        foreach ($content[$elementName] as $key => $value) {
          foreach ($value as $key2 => $v) {
            if (is_array($v)) {
              if (array_key_exists('value', $v)) {
                $retval[$key][$v['ID']] = $v['value'];
              }
              else {
                $retval[$key][$key2] = $v;
              }
            }
            else {
              $retval[$key][$key2] = $v;
            }
          }
        }
        return $retval;
      }
      // If element is not array.
      else {
        // Return value as is.
        return $content[$elementName];
      }
    }
    // If keys are numeric, we know that we need to decode the last
    // item with id's / names in array.
    elseif (self::numericKeys($content)) {
      // Loop content.
      foreach ($content as $value) {
        // If content is not array, it means that content is returnable as is.
        if (!is_array($value)) {
          return $value;
        }

        // If value is an array, then we need to return desired element value.
        if ($value['ID'] == $elementName) {
          $retval = htmlspecialchars_decode($value['value'] ?? '');

          if ($type == 'boolean') {
            if ($retval == 'true') {
              $retval = '1';
            }
            elseif ($retval == 'false') {
              $retval = '0';
            }
            else {
              $retval = '0';
            }
          }

          return $retval;
        }
      }
    }
    else {
      // If no item is specified with given name.
      return NULL;
    }
    // shouldn't get here that often.
    return NULL;
  }

  /**
   * Parse incorrect json string & decode.
   *
   * @param array $atvDocument
   *   Document structure.
   *
   * @return array
   *   Decoded content.
   */
  public function getAtvDocumentContent(array $atvDocument): array {
    if (is_string($atvDocument['content'])) {
      $replaced = str_replace("'", "\"", $atvDocument['content']);
      $replaced = str_replace("False", "false", $replaced);
      $replaced = str_replace("True", "true", $replaced);

      return Json::decode($replaced);
    }
    return $atvDocument['content'];
  }

  /**
   * Set content item to given document.
   *
   * @param array $atvDocument
   *   Document.
   * @param array $atvDocumentContent
   *   Content.
   *
   * @return array
   *   Added array.
   */
  public function setAtvDocumentContent(array $atvDocument, array $atvDocumentContent): array {
    $atvDocument['content'] = $atvDocumentContent;
    return $atvDocument;
  }

  /**
   * Format data type from item types.
   *
   * Use default value & value callback if applicable.
   *
   * @param array $itemTypes
   *   Item types for this value.
   * @param mixed $itemValue
   *   Value itself.
   * @param mixed $defaultValue
   *   Default value used if no value given. Configurable in typed data.
   * @param mixed $valueCallback
   *   Callback to handle value formatting. Configurable in typed data.
   *
   * @return mixed
   *   Formatted value.
   */
  public static function getItemValue(array $itemTypes, mixed $itemValue, mixed $defaultValue, mixed $valueCallback): mixed {

    // Support new value callback format to use either service or class.
    if ($valueCallback) {
      if (isset($valueCallback['service'])) {
        $fullItemValueService = \Drupal::service($valueCallback['service']);
        $funcName = $valueCallback['method'];
        $itemValue = $fullItemValueService->$funcName($itemValue, $valueCallback['arguments'] ?? []);
      }
      elseif (isset($valueCallback['class'])) {
        $funcName = $valueCallback['method'];
        $itemValue = $valueCallback['class']::$funcName($itemValue, $valueCallback['arguments'] ?? []);
      }
      else {
        // But still support old way to just call function.
        $itemValue = call_user_func($valueCallback, $itemValue);
      }
    }

    // If value is null, try to set default value from config.
    if (is_null($itemValue) && $defaultValue !== NULL) {
      $itemValue = $defaultValue;
    }

    if ($itemTypes['dataType'] === 'string' && $itemTypes['jsonType'] !== 'bool') {
      $itemValue = $itemValue . "";
    }

    if ($itemTypes['dataType'] === 'string' && $itemTypes['jsonType'] === 'bool') {
      if ($itemValue === FALSE) {
        $itemValue = 'false';
      }
      if ($itemValue === '0') {
        $itemValue = 'false';
      }
      if ($itemValue === 0) {
        $itemValue = 'false';
      }
      if ($itemValue === TRUE) {
        $itemValue = 'true';
      }
      if ($itemValue === '1') {
        $itemValue = 'true';
      }
      if ($itemValue === 1) {
        $itemValue = 'true';
      }
      if ($itemValue == 'Yes') {
        $itemValue = 'true';
      }
      if ($itemValue == 'No') {
        $itemValue = 'false';
      }
    }

    if ($itemTypes['jsonType'] == 'int') {
      $itemValue = str_replace('_', '', $itemValue);
    }

    return $itemValue;
  }

  /**
   * Get field values from full item callback.
   *
   * @param array $fullItemValueCallback
   *   Callback config.
   * @param \Drupal\Core\TypedData\TypedDataInterface $property
   *   Property.
   *
   * @return array
   *   Full item callback array.
   */
  public static function getFieldValuesFromFullItemCallback(
    array $fullItemValueCallback,
    TypedDataInterface $property
  ): mixed {
    $fieldValues = [];
    if (isset($fullItemValueCallback['service'])) {
      $fullItemValueService = \Drupal::service($fullItemValueCallback['service']);
      $funcName = $fullItemValueCallback['method'];
      $fieldValues = $fullItemValueService->$funcName($property, $fullItemValueCallback['arguments'] ?? []);
    }
    else {
      if (isset($fullItemValueCallback['class'])) {
        $funcName = $fullItemValueCallback['method'];
        $fieldValues = $fullItemValueCallback['class']::$funcName($property, $fullItemValueCallback['arguments'] ?? []);
      }
    }
    return $fieldValues;
  }

  /**
   * Get field values from full item callback.
   *
   * @param array $fullItemValueCallback
   *   Callback config.
   * @param array $content
   *   Content.
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $definition
   *   Definition.
   * @param array $arguments
   *   Possible arguments for value callback.
   *
   * @return array
   *   Full item callback array.
   */
  public function getWebformDataFromContent(
    array $fullItemValueCallback,
    array $content,
    DataDefinitionInterface $definition,
    array $arguments
  ): mixed {
    $fieldValues = [];
    if ($fullItemValueCallback['service']) {
      $fullItemValueService = \Drupal::service($fullItemValueCallback['service']);
      $funcName = $fullItemValueCallback['method'];

      $fieldValues = $fullItemValueService->$funcName($definition, $content, $arguments);
    }
    else {
      if ($fullItemValueCallback['class']) {
        $funcName = $fullItemValueCallback['method'];
        $fieldValues = $fullItemValueCallback['class']::$funcName($definition, $content, $arguments);
      }
    }
    return $fieldValues;
  }

  /**
   * Extracts data from ATV document compensation field.
   *
   * @param array $content
   *   ATV document in array form.
   * @param array $keys
   *   Array with IDs that the function will look for.
   *
   * @return array
   *   Assocative arrow with the results if they are found.
   */
  public static function extractDataForWebForm(array $content, array $keys) {
    $values = [];
    if (!isset($content['compensation'])) {
      return $values;
    }

    foreach ($content['compensation'] as $key => $item) {
      if (is_numeric($key)) {
        if (in_array($item['ID'], $keys) && !in_array($item['ID'], $values)) {
          $values[$item['ID']] = $item['value'];
        }
      }
      else {
        if (!is_array($item)) {
          $values[$key] = $item;
          continue;
        }
        foreach ($item as $key2 => $item2) {
          if (!is_array($item2)) {
            $values[$key2] = $item2;
          }
          elseif (AtvSchema::numericKeys($item2)) {
            foreach ($item2 as $key3 => $item3) {
              if (AtvSchema::numericKeys($item3)) {
                foreach ($item3 as $item4) {
                  if (in_array($item4['ID'], $keys) && !array_key_exists($item4['ID'], $values)) {
                    $values[$item4['ID']] = $item4['value'];
                  }
                }
              }
              else {
                if (isset($item3['ID']) && in_array($item3['ID'], $keys) && !array_key_exists($item3['ID'], $values)) {
                  $values[$item3['ID']] = $item3['value'];
                }
              }
            }
          }
          else {
            if (is_numeric($key2)) {
              if (in_array($item2['ID'], $keys) && !in_array($item2['ID'], $values)) {
                $values[$item2['ID']] = $item2['value'];
              }
            }
          }
        }
      }
    }
    return $values;
  }

  /**
   * Extracts data from ATV document compensation field.
   *
   * @param Drupal\Core\TypedData\DataDefinitionInterface $definition
   *   Field definition.
   * @param array $content
   *   ATV data.
   * @param array $arguments
   *   Arguments for method.
   *
   * @return array
   *   Assocative array with fields.
   *
   * @throws \Exception
   */
  public function returnRelations(DataDefinitionInterface $definition, array $content, array $arguments): array {
    /*
     * Fields in relations array:
     * master: Field name in ATV
     * slave: Field that exists in webform and is calculated based on master.
     * type: Type of the slave field.
     */
    $relations = $arguments['relations'];
    $pathArray = $definition->getSetting('jsonPath');
    $elementName = array_pop($pathArray);
    // Pick up the value for the master field.
    $value = $this->getValueFromDocument($content, $pathArray, $elementName, $definition);
    $relatedValue = match ($relations['type']) {
      // We use values 1 and 0 for boolean fields in webform.
      'boolean' => $value ? 1 : 0,
      default => throw new \Exception('Unknown relation type.'),
    };
    $retval = [
      $relations['master'] => $value,
      $relations['slave'] => $relatedValue,
    ];
    return $retval;
  }

}
