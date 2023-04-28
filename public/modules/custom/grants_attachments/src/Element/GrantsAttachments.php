<?php

namespace Drupal\grants_attachments\Element;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\grants_attachments\AttachmentHandler;
use Drupal\grants_handler\ApplicationHandler;
use Drupal\webform\Element\WebformCompositeBase;
use Drupal\webform\Utility\WebformElementHelper;

/**
 * Provides a 'grants_attachments'.
 *
 * Webform composites contain a group of sub-elements.
 *
 * @FormElement("grants_attachments")
 *
 * @see \Drupal\webform\Element\WebformCompositeBase
 * @see \Drupal\grants_attachments\Element\GrantsAttachments
 */
class GrantsAttachments extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo(): array {
    return parent::getInfo() + ['#theme' => 'grants_attachments'];
  }

  // @codingStandardsIgnoreStart

  /**
   * Build webform element based on data in ATV document.
   *
   * @param array $element
   *   Element that is being processed.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param array $complete_form
   *   Full form.
   *
   * @return array[]
   *   Form API element for webform element.
   */
  public static function processWebformComposite(&$element, FormStateInterface $form_state, &$complete_form): array {

    $element['#tree'] = TRUE;
    $element = parent::processWebformComposite($element, $form_state, $complete_form);

    $submission = $form_state->getFormObject()->getEntity();
    $submissionData = $submission->getData();

    $triggeringElement = $form_state->getTriggeringElement();
    $storage = $form_state->getStorage();

    $arrayKey = $element['#webform_key'];
    if (isset($element['#parents'][1]) && $element['#parents'][1] == 'items') {
      $arrayKey .=  '_' . $element['#parents'][2];
    }

    if (isset($storage['errors'][$arrayKey])) {
      $errors = $storage['errors'][$arrayKey];
      $element['#attributes']['class'][] = $errors['label'];
      $element['#attributes']['error_label'] = $errors['label'];
    }

    // Attachment has been deleted, show default componenet state.
    if (isset($storage['deleted_attachments'][$arrayKey]) && $storage['deleted_attachments'][$arrayKey]) {
      unset($element['attachmentName']);
      return $element;
    }

    if (isset($submissionData[$element['#webform_key']]) && is_array($submissionData[$element['#webform_key']])) {

      $dataForElement = $element['#value'];

      // When user goes to previous step etc. we might lose the additional data for the just
      // uploaded elements. As we are saving these to storage - let's find
      // out the actual data the and use it.
      $fid = $dataForElement['attachment']['fids'] ?? NULL;

      if ($dataForElement['integrationID'] && isset($storage['fids_info']) && $dataForElement) {
        foreach ($storage['fids_info'] as $finfo) {
          if ($dataForElement['integrationID'] == $finfo['integrationID']) {
            $dataForElement = $finfo;
            break;
          }
        }
      }

      $uploadStatus = $dataForElement['fileStatus'] ?? NULL;

      if (isset($dataForElement["fileType"])) {
        $element["fileType"]["#value"] = $dataForElement["fileType"];
      }
      elseif (isset($element["#filetype"])) {
        $element["fileType"]["#value"] = $element["#filetype"];
      }

      if (isset($dataForElement["integrationID"]) && !empty($dataForElement["integrationID"])) {
        $element["integrationID"]["#value"] = $dataForElement["integrationID"];
        $element["fileStatus"]["#value"] = 'uploaded';
      }

      if (isset($dataForElement['isDeliveredLater'])) {
        $element["isDeliveredLater"]["#default_value"] = $dataForElement['isDeliveredLater'] == 'true';
        if ($element["isDeliveredLater"]["#default_value"] == TRUE) {
          $element["fileStatus"]["#value"] = 'deliveredLater';
        }
        if ($dataForElement['isDeliveredLater'] == '1') {
          $element["isDeliveredLater"]['#default_value'] = TRUE;
        }
      }
      if (isset($dataForElement['isIncludedInOtherFile'])) {
        $element["isIncludedInOtherFile"]["#default_value"] = ($dataForElement['isIncludedInOtherFile'] == 'true' || $dataForElement['isIncludedInOtherFile'] == '1');
        if ($element["isIncludedInOtherFile"]["#default_value"] == TRUE) {
          $element["fileStatus"]["#value"] = 'otherFile';
        }
      }
      if (!empty($dataForElement['fileName']) || !empty($dataForElement['attachmentName'])) {
        $element['attachmentName'] = [
          '#type' => 'textfield',
          '#default_value' => $dataForElement['fileName'] ?? $dataForElement['attachmentName'],
          '#value' => $dataForElement['fileName'] ?? $dataForElement['attachmentName'],
          '#readonly' => TRUE,
          '#attributes' => ['readonly' => 'readonly'],
        ];

        $element["isIncludedInOtherFile"]["#disabled"] = TRUE;
        $element["isDeliveredLater"]["#disabled"] = TRUE;

        unset($element['isDeliveredLater']['#states']);
        unset($element['isIncludedInOtherFile']['#states']);

        $element["attachment"]["#access"] = FALSE;
        $element["attachment"]["#readonly"] = TRUE;
        $element["attachment"]["#attributes"] = ['readonly' => 'readonly'];

        if (isset($element["isNewAttachment"])) {
          $element["isNewAttachment"]["#value"] = FALSE;
        }

        $element["fileStatus"]["#value"] = 'uploaded';

        // $element["description"]["#disabled"] = TRUE;
        if ($uploadStatus !== 'justUploaded') {
          $element["description"]["#readonly"] = TRUE;
          $element["description"]["#attributes"] = ['readonly' => 'readonly'];
        }

        if (
          isset($dataForElement['fileType'])
          && $dataForElement['fileType'] != '45'
          && (isset($submissionData['status']) && $submissionData['status'] === 'DRAFT')
        ) {
          $element['deleteItem'] = [
            '#type' => 'submit',
            '#name' => 'delete_' . $arrayKey,
            '#value' => t('Delete attachment'),
            '#submit' => [
              ['\Drupal\grants_attachments\Element\GrantsAttachments', 'deleteAttachmentSubmit'],
            ],
            '#limit_validation_errors' => [[$element['#webform_key']]],
            '#ajax' => [
              'callback' => [
                '\Drupal\grants_attachments\Element\GrantsAttachments',
                'deleteAttachment',
              ],
              'wrapper' => $element["#webform_id"],
            ],
          ];
        }

      }
      if (isset($dataForElement['description'])) {
        $element["description"]["#default_value"] = $dataForElement['description'];
      }

      if (isset($dataForElement['fileType']) && $dataForElement['fileType'] == '45') {
        if (isset($dataForElement['attachmentName']) && $dataForElement['attachmentName'] !== "") {
          $element["fileStatus"]["#value"] = 'uploaded';
        }
      }

      // Final override to rule them all.
      if ($uploadStatus === 'justUploaded') {
        $element["fileStatus"]["#value"] = 'justUploaded';
      }
    }

    $element['#prefix'] = '<div class="' . $element["#webform_id"] . '">';
    $element['#suffix'] = '</div>';

    return $element;
  }

  // @codingStandardsIgnoreEnd

  /**
   * Form elements for attachments.
   *
   * @todo Use description field always and poplate contents from field title.
   * @todo Allowed file extensions for attachments??
   *
   * {@inheritdoc}
   */
  public static function getCompositeElements(array $element): array {
    $sessionHash = sha1(\Drupal::service('session')->getId());
    $upload_location = 'private://grants_attachments/' . $sessionHash;
    $maxFileSizeInBytes = (1024 * 1024) * 32;

    $elements = [];

    $uniqId = Html::getUniqueId('composite-attachment');

    $elements['attachment'] = [
      '#type' => 'managed_file',
      '#title' => t('Attachment'),
      '#multiple' => FALSE,
      '#uri_scheme' => 'private',
      '#file_extensions' => 'doc,docx,gif,jpg,jpeg,pdf,png,ppt,pptx,rtf,txt,xls,xlsx,zip',
      '#upload_validators' => [
        'file_validate_extensions' => ['doc docx gif jpg jpeg pdf png ppt pptx rtf txt xls xlsx zip'],
        'file_validate_size' => [$maxFileSizeInBytes],
      ],
      '#upload_location' => $upload_location,
      '#sanitize' => TRUE,
      '#states' => [
        'disabled' => [
          '[data-webform-composite-attachment-checkbox="' . $uniqId . '"]' => ['checked' => TRUE],
        ],
      ],
      '#element_validate' => ['\Drupal\grants_attachments\Element\GrantsAttachments::validateUpload'],
    ];

    $elements['attachmentName'] = [
      '#type' => 'textfield',
      '#readonly' => TRUE,
      '#attributes' => ['readonly' => 'readonly'],
    ];

    $elements['description'] = [
      '#type' => 'textfield',
      '#title' => t('Attachment description'),
    ];
    $elements['isDeliveredLater'] = [
      '#type' => 'checkbox',
      '#title' => t('Attachment will delivered at later time'),
      '#element_validate' => ['\Drupal\grants_attachments\Element\GrantsAttachments::validateDeliveredLaterCheckbox'],
      '#attributes' => [
        'data-webform-composite-attachment-isDeliveredLater' => $uniqId,
        'data-webform-composite-attachment-checkbox' => $uniqId,
      ],
      '#states' => [
        'enabled' => [
          '[data-webform-composite-attachment-inOtherFile="' . $uniqId . '"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $elements['isIncludedInOtherFile'] = [
      '#type' => 'checkbox',
      '#title' => t('Attachment already delivered'),
      '#attributes' => [
        'data-webform-composite-attachment-inOtherFile' => $uniqId,
        'data-webform-composite-attachment-checkbox' => $uniqId,
      ],
      '#states' => [
        'enabled' => [
          '[data-webform-composite-attachment-isDeliveredLater="' . $uniqId . '"]' => ['checked' => FALSE],
        ],
      ],
      '#element_validate' => [
        '\Drupal\grants_attachments\Element\GrantsAttachments::validateIncludedOtherFileCheckbox',
        '\Drupal\grants_attachments\Element\GrantsAttachments::validateElements',
      ],
    ];
    $elements['fileStatus'] = [
      '#type' => 'hidden',
      '#value' => NULL,
    ];
    $elements['fileType'] = [
      '#type' => 'hidden',
      '#value' => NULL,
    ];
    $elements['integrationID'] = [
      '#type' => 'hidden',
      '#value' => NULL,
    ];

    return $elements;
  }

  /**
   * Submit handler for deleting attachments.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public static function deleteAttachmentSubmit(array $form, FormStateInterface $form_state): array {
    $triggeringElement = $form_state->getTriggeringElement();

    $form_state->setRebuild(TRUE);
    $attachmentField = $triggeringElement['#parents'];
    $attachmentField[count($attachmentField) - 1] = 'attachment';

    $multiValue = FALSE;
    $multiValueKey = NULL;
    if (isset($attachmentField[1]) && $attachmentField[1] == 'items') {
      $multiValue = TRUE;
      $multiValueKey = $attachmentField[2];
    }

    array_pop($attachmentField);
    if ($multiValue) {
      $attachmentField = [$attachmentField[0], $multiValueKey];
    }

    // Get attachment field info.
    $attachmentParent = $form_state->getValue($attachmentField);
    $form_state->setValue($attachmentField, []);
    $storage = $form_state->getStorage();

    // Array key depending if multi-value or single attachment.
    $arrayKey = $multiValue ? $attachmentField[0] . '_' . $multiValueKey : reset($attachmentField);

    $storage['deleted_attachments'][$arrayKey] = $attachmentParent;
    $form_state->setStorage($storage);

    return $form;
  }

  /**
   * Ajax callback for attachment deletion.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public static function deleteAttachment(array $form, FormStateInterface $form_state): AjaxResponse {
    $triggeringElement = $form_state->getTriggeringElement();

    $parent = reset($triggeringElement['#parents']);
    $elem = $form['elements']['lisatiedot_ja_liitteet']['liitteet'][$parent];
    $selector = '.' . $elem['#webform_id'];

    if (isset($triggeringElement['#parents'][1]) && $triggeringElement['#parents'][1] == 'items') {
      $selector = '#muu_liite_table';
    }

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand($selector, $elem));
    return $response;
  }

  /**
   * Find items recursively from an array.
   *
   * @param array $haystack
   *   Search from.
   * @param string $needle
   *   What to search.
   *
   * @return \Generator
   *   Added value.
   */
  public static function recursiveFind(array $haystack, string $needle): \Generator {
    $iterator = new \RecursiveArrayIterator($haystack);
    $recursive = new \RecursiveIteratorIterator(
      $iterator,
      \RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($recursive as $key => $value) {
      if ($key === $needle) {
        yield $value;
      }
    }
  }

  /**
   * Validate & upload file attachment.
   *
   * @param array $element
   *   Element tobe validated.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param array $form
   *   The form.
   *
   * @return bool|null
   *   Success or not.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public static function validateUpload(array &$element, FormStateInterface $form_state, array &$form): bool|null {

    $webformKey = $element["#parents"][0];
    $triggeringElement = $form_state->getTriggeringElement();
    $isRemoveAction = str_contains($triggeringElement["#name"], 'attachment_remove_button');

    // Work only on uploaded files.
    if (isset($element["#files"]) && !empty($element["#files"])) {
      $multiValueField = FALSE;
      $validatingTriggeringElementParent = FALSE;
      $hasSameRootElement = reset($triggeringElement['#parents']) === reset($element['#parents']);

      // Reset index.
      $index = 0;

      /** @var \Drupal\webform\WebformSubmissionForm $form_object */
      $form_object = $form_state->getFormObject();
      /** @var \Drupal\webform\WebformSubmissionInterface $webform_submission */
      $webformSubmission = $form_object->getEntity();
      // Get data from webform.
      $webformData = $webformSubmission->getData();

      // Figure out paths on form & element.
      $valueParents = $element["#parents"];
      array_pop($valueParents);

      $arrayParents = $element["#array_parents"];
      array_splice($arrayParents, -4);

      // Get webform data element from submitted data.
      if (in_array('items', $valueParents)) {
        end($valueParents);
        $index = prev($valueParents);
        $webformDataElement = $webformData[$webformKey][$index] ?? NULL;
        // ...
        $fid = array_key_first($element["#files"]);
        $validID = $webformDataElement['attachment'] == $fid;

        if (!$validID) {
          foreach ($webformData[$webformKey] as $item) {
            if ($item['attachment'] == $fid) {
              $webformDataElement = $item;
              break;
            }
          }
        }
        $validatingTriggeringElementParent = in_array($index, $triggeringElement['#parents']);
        $multiValueField = TRUE;
      }
      else {
        $webformDataElement = $webformData[$webformKey];
      }

      // If we already have uploaded this file now, lets not do it again.
      if (!$isRemoveAction && isset($webformDataElement["fileStatus"]) && $webformDataElement["fileStatus"] == 'justUploaded') {
        // It seems that this is only place where we have description field in
        // form values. Somehow this is not available in handler anymore.
        // it's not even available, when initially processing the upload
        // because then the $element is file upload.
        $formValue = $form_state->getValue($webformKey);
        // So we set the description here after cleaning.
        // Also check if this is multivalue form array or not.
        $webformDataElement['description'] = Xss::filter($formValue['description'] ?? $formValue[$index]['description']);
        // And set webform element back to form state.
        $form_state->setValue([...$valueParents], $webformDataElement);
      }

      // If no application number, we cannot validate.
      // We should ALWAYS have it though at this point.
      if (!isset($webformData['application_number'])) {
        return NULL;
      }
      // Get application number from data.
      $application_number = $webformData['application_number'];

      /** @var \Drupal\grants_handler\ApplicationHandler $applicationHandler */
      $applicationHandler = \Drupal::service('grants_handler.application_handler');
      /** @var \Drupal\helfi_atv\AtvService $atvService */
      $atvService = \Drupal::service('helfi_atv.atv_service');

      // If upload button is clicked.
      if (str_contains($triggeringElement["#name"], 'attachment_upload_button')) {

        if (!$hasSameRootElement || ($multiValueField && !$validatingTriggeringElementParent)) {
          return NULL;
        }

        // Try to find filetype via array parents.
        $formFiletype = NestedArray::getValue($form, [
          ...$arrayParents,
          '#filetype',
        ]);
        // If not, then brute force value from form.
        if (empty($formFiletype) && $formFiletype !== '0') {
          foreach (self::recursiveFind($form, $webformKey) as $value) {
            if ($value != NULL) {
              $formFiletype = $value['#filetype'];
            }
          }
        }

        foreach ($element["#files"] as $file) {
          try {
            // Get Document for this application.
            $atvDocument = $applicationHandler->getAtvDocument($application_number);

            // Upload attachment to document.
            $attachmentResponse = $atvService->uploadAttachment($atvDocument->getId(), $file->getFilename(), $file);

            // Remove server url from integrationID.
            $baseUrl = $atvService->getBaseUrl();
            $baseUrlApps = str_replace('agw', 'apps', $baseUrl);
            // Remove server url from integrationID.
            // We need to make sure that the integrationID gets removed inside &
            // outside the azure environment.
            $integrationId = str_replace($baseUrl, '', $attachmentResponse['href']);
            $integrationId = str_replace($baseUrlApps, '', $integrationId);

            $appParam = ApplicationHandler::getAppEnv();
            if ($appParam !== 'PROD') {
              $integrationId = '/' . $appParam . $integrationId;
            }

            // Set values to form.
            $form_state->setValue([
              ...$valueParents,
              'integrationID',
            ], $integrationId);

            $form_state->setValue([
              ...$valueParents,
              'fileStatus',
            ], 'justUploaded');

            $form_state->setValue([
              ...$valueParents,
              'isDeliveredLater',
            ], '0');

            $form_state->setValue([
              ...$valueParents,
              'isIncludedInOtherFile',
            ], '0');

            $form_state->setValue([
              ...$valueParents,
              'fileName',
            ], $file->getFilename());

            $form_state->setValue([
              ...$valueParents,
              'attachmentName',
            ], $file->getFilename());

            $form_state->setValue([
              ...$valueParents,
              'attachmentIsNew',
            ], TRUE);

            $form_state->setValue([
              ...$valueParents,
              'fileType',
            ], $formFiletype);

            $storage = $form_state->getStorage();
            $storage['fids_info'][$file->id()] = [
              'integrationID' => $integrationId,
              'fileStatus'    => 'justUploaded',
              'isDeliveredLater' => '0',
              'isIncludedInOtherFile' => '0',
              'fileName' => $file->getFileName(),
              'attachmentIsNew' => TRUE,
              'attachmentName' => $file->getFileName(),
              'fileType' => $formFiletype,
              'attachment' => $file->id(),
            ];

            $form_state->setStorage($storage);

          }
          catch (\Exception $e) {
            // Set error to form.
            $form_state->setError($element, t('File upload failed, error has been logged.'));
            // Log error.
            \Drupal::logger('grants_attachments')->error($e->getMessage());
            // And set webform element back to form state.
            $form_state->unsetValue($valueParents);
            $form_state->setValue([...$valueParents], []);
            if ($multiValueField) {
              $tempKey = [reset($valueParents), 'items', $index];
              $form_state->unsetValue($tempKey);
              $form_state->setValue($tempKey, []);
            }

            $element['#value'] = NULL;
            $element['#default_value'] = NULL;

            if (isset($element['#files'])) {
              foreach ($element['#files'] as $delta => $file) {
                unset($element['file_' . $delta]);
              }
            }

            unset($element['#label_for']);
            $file->delete();
            return FALSE;
          }
        }
      }
      elseif ($isRemoveAction) {

        // Validate function is looping all file fields.
        // Check if we are actually currently trying to delete a
        // field which triggered the action.
        if (!$hasSameRootElement || ($multiValueField && !$validatingTriggeringElementParent)) {
          $form_state->setValue([...$valueParents], $webformDataElement);
          return NULL;
        }

        try {
          // Delete attachment via integration id.
          $cleanIntegrationId = AttachmentHandler::cleanIntegrationId($webformDataElement["integrationID"]);
          if (!$cleanIntegrationId && reset($element["#files"])) {
            $storage = $form_state->getStorage();

            $valueToCheck = $storage['fids_info'][$fid]['integrationID'] ?? NULL;
            unset($storage['fids_info'][$fid]['integrationID']);
            $form_state->setStorage($storage);
            $cleanIntegrationId = AttachmentHandler::cleanIntegrationId($valueToCheck);
          }
          if ($cleanIntegrationId) {
            $atvService->deleteAttachmentViaIntegrationId($cleanIntegrationId);
          }
        }
        catch (\Throwable $t) {
          \Drupal::logger('grants_attachments')
            ->error('Attachment deleting failed. Error: @error', ['@error' => $t->getMessage()]);
        }
        finally {
          // And set webform element back to form state.
          $form_state->setValue([...$valueParents], []);
        }
      }
    }
    return NULL;
  }

  /**
   * Validates a composite element.
   */
  public static function validateWebformComposite(&$element, FormStateInterface $form_state, &$complete_form) {
    // IMPORTANT: Must get values from the $form_states since sub-elements
    // may call $form_state->setValueForElement() via their validation hook.
    // @see \Drupal\webform\Element\WebformEmailConfirm::validateWebformEmailConfirm
    // @see \Drupal\webform\Element\WebformOtherBase::validateWebformOther
    $value = NestedArray::getValue($form_state->getValues(), $element['#parents']);

    if (in_array('items', $element['#parents'])) {
      return;
    }

    // Only validate composite elements that are visible.
    if (Element::isVisibleElement($element)) {
      // Validate required composite elements.
      $composite_elements = static::getCompositeElements($element);
      $composite_elements = WebformElementHelper::getFlattened($composite_elements);
      foreach ($composite_elements as $composite_key => $composite_element) {
        $is_required = !empty($element[$composite_key]['#required']);
        $is_empty = (isset($value[$composite_key]) && $value[$composite_key] === '');
        if ($is_required && $is_empty) {
          WebformElementHelper::setRequiredError($element[$composite_key], $form_state);
        }
      }
    }

    // Clear empty composites value.
    if (is_array($value) && empty(array_filter($value))) {
      $element['#value'] = NULL;
      $form_state->setValueForElement($element, NULL);
    }
  }

  /**
   * Validate Checkbox.
   *
   * @param array $element
   *   Validated element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param array $complete_form
   *   Form itself.
   */
  public static function validateDeliveredLaterCheckbox(
    array &$element,
    FormStateInterface $form_state,
    array &$complete_form) {

    $file = $form_state->getValue([
      $element["#parents"][0],
      'attachment',
    ]);
    $isDeliveredLaterCheckboxValue = $form_state->getValue([
      $element["#parents"][0],
      'isDeliveredLater',
    ]);
    $integrationID = $form_state->getValue([
      $element["#parents"][0],
      'integrationID',
    ]);

    if ($file !== NULL && $isDeliveredLaterCheckboxValue === '1') {
      if (empty($integrationID)) {
        $form_state->setError($element, t('You cannot send file and have it delivered later'));
      }
    }
  }

  /**
   * Validate checkbox.
   *
   * @param array $element
   *   Validated element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param array $complete_form
   *   Form itself.
   */
  public static function validateIncludedOtherFileCheckbox(
    array &$element,
    FormStateInterface $form_state,
    array &$complete_form) {

    $file = $form_state->getValue([
      $element["#parents"][0],
      'attachment',
    ]);
    $checkboxValue = $form_state->getValue([
      $element["#parents"][0],
      'isIncludedInOtherFile',
    ]);

    $integrationID = $form_state->getValue([
      $element["#parents"][0],
      'integrationID',
    ]);

    if ($file !== NULL && $checkboxValue === '1') {
      if (empty($integrationID)) {
        $form_state->setError($element, t('You cannot send file and have it in another file'));
      }
    }

  }

  /**
   * Validate composite elements valid state.
   *
   * @param array $element
   *   Validated element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param array $complete_form
   *   Form itself.
   */
  public static function validateElements(
    array &$element,
    FormStateInterface $form_state,
    array &$complete_form
  ) {

    $triggerngElement = $form_state->getTriggeringElement();

    if (str_contains($triggerngElement['#name'], 'delete_')) {
      return;
    }

    // These are not required for muut liitteet as it's optional.
    $rootParent = reset($element['#parents']);
    if ($rootParent === 'muu_liite') {
      return;
    }

    $value = NestedArray::getValue($form_state->getValues(), [reset($element['#parents'])]);
    $arrayParents = $element['#array_parents'];
    array_pop($arrayParents);
    $parent = NestedArray::getValue($complete_form, $arrayParents);
    // Custom validation logic.
    if ($value !== NULL && !empty($value)) {
      // If attachment is uploaded, make sure no other field is selected.
      if (isset($value['attachment']) && is_int($value['attachment'])) {
        if ($value['isDeliveredLater'] === "1") {
          $form_state->setError($element, t('@fieldname has file added, it cannot be added later.', [
            '@fieldname' => $parent['#title'],
          ]));
        }
        if ($value['isIncludedInOtherFile'] === "1") {
          $form_state->setError($element, t('@fieldname has file added, it cannot belong to other file.', [
            '@fieldname' => $parent['#title'],
          ]));
        }
      }
      else {
        // No attachments or checkboxes.
        if (!empty($value) && !isset($value['attachment']) && ($value['attachment'] === NULL && $value['attachmentName'] === '')) {
          if (empty($value['isDeliveredLater']) && empty($value['isIncludedInOtherFile'])) {
            $form_state->setError($element, t('@fieldname has no file uploaded, it must be either delivered later or be included in other file.', [
              '@fieldname' => $parent['#title'],
            ]));
          }
        }
      }
      // Both checkboxes cannot be selected.
      if ($value['isDeliveredLater'] === "1" && $value['isIncludedInOtherFile'] === "1") {
        $form_state->setError($element, t("@fieldname you can't select both checkboxes.", [
          '@fieldname' => $parent['#title'],
        ]));
      }
    }
  }

}
