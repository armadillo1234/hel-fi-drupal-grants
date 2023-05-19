<?php

namespace Drupal\grants_profile\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\grants_profile\GrantsProfileService;
use Drupal\grants_profile\TypedData\Definition\GrantsProfilePrivatePersonDefinition;
use Drupal\helfi_helsinki_profiili\HelsinkiProfiiliUserData;
use Drupal\helfi_atv\AtvDocumentNotFoundException;
use Drupal\helfi_atv\AtvFailedToConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Grants Profile form.
 */
class GrantsProfileFormPrivatePerson extends GrantsProfileFormBase {

  use StringTranslationTrait;

  /**
   * Drupal\Core\TypedData\TypedDataManager definition.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected TypedDataManager $typedDataManager;

  /**
   * Access to grants profile services.
   *
   * @var \Drupal\grants_profile\GrantsProfileService
   */
  protected GrantsProfileService $grantsProfileService;

  /**
   * Helsinki profile service.
   *
   * @var \Drupal\helfi_helsinki_profiili\HelsinkiProfiiliUserData
   */
  protected HelsinkiProfiiliUserData $helsinkiProfiiliUserData;

  /**
   * Constructs a new GrantsProfileForm object.
   *
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   Data manager.
   * @param \Drupal\grants_profile\GrantsProfileService $grantsProfileService
   *   Profile service.
   * @param \Drupal\helfi_helsinki_profiili\HelsinkiProfiiliUserData $helsinkiProfiiliUserData
   *   Data for Helsinki Profile.
   */
  public function __construct(
    TypedDataManager $typed_data_manager,
    GrantsProfileService $grantsProfileService,
    HelsinkiProfiiliUserData $helsinkiProfiiliUserData
  ) {
    $this->typedDataManager = $typed_data_manager;
    $this->grantsProfileService = $grantsProfileService;
    $this->helsinkiProfiiliUserData = $helsinkiProfiiliUserData;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): GrantsProfileFormPrivatePerson|static {
    return new static(
      $container->get('typed_data_manager'),
      $container->get('grants_profile.service'),
      $container->get('helfi_helsinki_profiili.userdata')
    );
  }

  /**
   * Helper method so we can have consistent dialog options.
   *
   * @return string[]
   *   An array of jQuery UI elements to pass on to our dialog form.
   */
  public static function getDataDialogOptions(): array {
    return [
      'width' => '33%',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'grants_profile_private_person';
  }

  /**
   * {@inheritdoc}
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $selectedRoleData = $this->grantsProfileService->getSelectedRoleData();

    // Load grants profile.
    $grantsProfile = $this->grantsProfileService->getGrantsProfile($selectedRoleData, TRUE);

    // If no profile exist.
    if ($grantsProfile == NULL) {
      // Create one and.
      $grantsProfile = $this->grantsProfileService->createNewProfile($selectedRoleData);
    }

    if ($grantsProfile == NULL) {
      return [];
    }

    // Get content from document.
    $grantsProfileContent = $grantsProfile->getContent();

    $helsinkiProfileContent = $this->helsinkiProfiiliUserData->getUserProfileData();

    $storage = $form_state->getStorage();
    $storage['profileDocument'] = $grantsProfile;

    // Use custom theme hook.
    $form['#theme'] = 'own_profile_form_private_person';
    $form['#tree'] = TRUE;

    $form['#after_build'] = ['Drupal\grants_profile\Form\GrantsProfileFormPrivatePerson::afterBuild'];

    $form['newItem'] = [
      '#type' => 'hidden',
      '#value' => NULL,
    ];
    $newItem = $form_state->getValue('newItem');

    $address = $grantsProfileContent['addresses'][0] ?? NULL;

    // Make sure we have proper UUID as address id.
    if ($address && !$this->grantsProfileService->isValidUuid($address['address_id'])) {
      $address['address_id'] = Uuid::uuid4()->toString();
    }

    $form['addressWrapper'] = [
      '#type' => 'webform_section',
      '#title' => $this->t('Addresses'),
      '#prefix' => '<div id="addresses-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['addressWrapper']['street'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Street address'),
      '#default_value' => $address['street'] ?? '',
      '#required' => TRUE,
    ];
    $form['addressWrapper']['postCode'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Postal code'),
      '#default_value' => $address['postCode'] ?? '',
      '#required' => TRUE,
    ];
    $form['addressWrapper']['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City/town', [], ['context' => 'Profile Address']),
      '#default_value' => $address['city'] ?? '',
      '#required' => TRUE,
    ];
    $form['addressWrapper']['country'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Country', [], ['context' => 'Profile Address']),
      '#attributes' => ['readonly' => 'readonly'],
      '#default_value' => $address['country'] ?? 'Suomi',
      '#value' => $address['country'] ?? 'Suomi',
    ];
    // We need the delta / id to create delete links in element.
    $form['addressWrapper']['address_id'] = [
      '#type' => 'hidden',
      '#value' => $address['address_id'] ?? '',
    ];

    $form['phoneWrapper'] = [
      '#type' => 'webform_section',
      '#title' => $this->t('Phone number'),
      '#prefix' => '<div id="phone-wrapper">',
      '#suffix' => '</div>',
    ];
    $form['phoneWrapper']['phone_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone number'),
      '#default_value' => $grantsProfileContent['phone_number'] ?? '',
      '#required' => TRUE,
    ];

    $form['emailWrapper'] = [
      '#type' => 'webform_section',
      '#title' => $this->t('Email address'),
      '#prefix' => '<div id="email-wrapper">',
      '#suffix' => '</div>',
    ];
    $form['emailWrapper']['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email address'),
      '#default_value' => $grantsProfileContent['email'] ?? '',
      '#required' => TRUE,
    ];

    $this->addbankAccountBits($form, $form_state, $grantsProfileContent['bankAccounts'], $newItem);

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save own information'),
    ];

    $form['#profilecontent'] = $grantsProfileContent;
    $form['#helsinkiprofilecontent'] = $helsinkiProfileContent;
    $form_state->setStorage($storage);

    return $form;
  }

  /**
   * Ajax callback for removing item from form.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Form state.
   */
  public static function removeOne(array &$form, FormStateInterface $formState) {

    $triggeringElement = $formState->getTriggeringElement();
    [
      $fieldName,
      $deltaToRemove,
    ] = explode('--', $triggeringElement['#name']);

    $fieldValue = $formState->getValue($fieldName);

    if ($fieldName == 'bankAccountWrapper' && $fieldValue[$deltaToRemove]['bank']['confirmationFileName']) {
      $attachmentDeleteResults = self::deleteAttachmentFile($fieldValue[$deltaToRemove]['bank'], $formState);

      if ($attachmentDeleteResults) {
        \Drupal::messenger()
          ->addStatus('Bank account & verification attachment deleted.');
      }
      else {
        \Drupal::messenger()
          ->addError('Attachment deletion failed, error has been logged. Please contact customer support');
      }
    }
    // Remove item from items.
    unset($fieldValue[$deltaToRemove]);
    $formState->setValue($fieldName, $fieldValue);
    $formState->setRebuild();

  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Forms state.
   */
  public function addOne(array &$form, FormStateInterface $formState) {
    $triggeringElement = $formState->getTriggeringElement();
    [
      $fieldName,
    ] = explode('--', $triggeringElement['#name']);

    $formState
      ->setValue('newItem', $fieldName);

    // Since our buildForm() method relies on the value of 'num_names' to
    // generate 'name' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $formState
      ->setRebuild();
  }

  /**
   * Validate & upload file attachment.
   *
   * @param array $element
   *   Element tobe validated.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Form state.
   * @param array $form
   *   The form.
   */
  public static function validateUpload(array &$element, FormStateInterface $formState, array &$form) {

    $storage = $formState->getStorage();
    $grantsProfileDocument = $storage['profileDocument'];

    $triggeringElement = $formState->getTriggeringElement();

    /** @var \Drupal\helfi_atv\AtvService $atvService */
    $atvService = \Drupal::service('helfi_atv.atv_service');

    // Figure out paths on form & element.
    $valueParents = $element["#parents"];

    if (str_contains($triggeringElement["#name"], 'confirmationFile_upload_button')) {
      foreach ($element["#files"] as $file) {
        try {

          // Upload attachment to document.
          $attachmentResponse = $atvService->uploadAttachment(
            $grantsProfileDocument->getId(),
            $file->getFilename(),
            $file
          );

          $storage['confirmationFiles'][$valueParents[1]] = $attachmentResponse;

        }
        catch (AtvDocumentNotFoundException | AtvFailedToConnectException | GuzzleException $e) {
          // Set error to form.
          $formState->setError($element, 'File upload failed, error has been logged.');
          // Log error.
          \Drupal::logger('grants_profile')->error($e->getMessage());

        }
      }
    }

    $formState->setStorage($storage);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $formState) {

    $triggeringElement = $formState->getTriggeringElement();

    if ($triggeringElement["#id"] !== 'edit-actions-submit') {
      return;
    }

    $storage = $formState->getStorage();
    /** @var \Drupal\helfi_atv\AtvDocument $grantsProfileDocument */
    $grantsProfileDocument = $storage['profileDocument'];

    if (!$grantsProfileDocument) {
      $this->messenger()->addError($this->t('grantsProfileContent not found!'));
      return;
    }

    $grantsProfileContent = $grantsProfileDocument->getContent();

    $values = $formState->getValues();
    $input = $formState->getUserInput();

    if (array_key_exists('addressWrapper', $input)) {
      $values["addressWrapper"] = $input["addressWrapper"];
    }

    if (array_key_exists('bankAccountWrapper', $input)) {
      $bankAccountArrayKeys = array_keys($input["bankAccountWrapper"]);
      $values["bankAccountWrapper"] = $input["bankAccountWrapper"];
    }

    $values = $this->cleanUpFormValues($values, $input, $storage);

    // Set clean values to form state.
    $formState->setValues($values);

    if (array_key_exists('addressWrapper', $values)) {
      unset($values["addressWrapper"]["actions"]);
      $grantsProfileContent['addresses'] = $values["addressWrapper"];
    }

    if (array_key_exists('bankAccountWrapper', $values)) {
      unset($values["bankAccountWrapper"]["actions"]);
      $grantsProfileContent['bankAccounts'] = $values["bankAccountWrapper"];
    }
    if (array_key_exists('phoneWrapper', $values)) {
      $grantsProfileContent['phone_number'] = $values["phoneWrapper"]['phone_number'];
    }
    if (array_key_exists('emailWrapper', $values)) {
      $grantsProfileContent['email'] = $values["emailWrapper"]['email'];
    }

    $this->validateBankAccounts($values, $formState);

    parent::validateForm($form, $formState);

    $grantsProfileDefinition = GrantsProfilePrivatePersonDefinition::create('grants_profile_private_person');
    // Create data object.
    $grantsProfileData = $this->typedDataManager->create($grantsProfileDefinition);
    $grantsProfileData->setValue($grantsProfileContent);
    // Validate inserted data.
    $violations = $grantsProfileData->validate();
    // If there's violations in data.
    if ($violations->count() != 0) {
      /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
      foreach ($violations as $violation) {
        // Print errors by form item name.
        $propertyPathArray = explode('.', $violation->getPropertyPath());
        $errorElement = NULL;
        $errorMesg = NULL;

        $propertyPath = '';

        if ($propertyPathArray[0] == 'addresses') {
          if (count($propertyPathArray) == 1) {
            $errorElement = $form["addressWrapper"];
            $errorMesg = 'You must add one address';
          }
          else {
            $propertyPath = 'addressWrapper][' . $propertyPathArray[2];
          }
        }
        elseif ($propertyPathArray[0] == 'bankAccounts') {
          if (count($propertyPathArray) == 1) {
            $errorElement = $form["bankAccountWrapper"];
            $errorMesg = 'You must add one bank account';
          }
          else {
            $propertyPath = 'bankAccountWrapper][' . $bankAccountArrayKeys[$propertyPathArray[1]] . '][bank][' . $propertyPathArray[2];
          }

        }
        elseif ($propertyPathArray[0] == 'email') {
          $propertyPath = 'emailWrapper][email';
        }
        else {
          $propertyPath = $violation->getPropertyPath();
        }

        if ($errorElement) {
          $formState->setError(
            $errorElement,
            $errorMesg
          );
        }
        else {
          $formState->setErrorByName(
            $propertyPath,
            $violation->getMessage()
          );
        }
      }
    }
    else {
      // Move addressData object to form_state storage.
      $formState->setStorage(['grantsProfileData' => $grantsProfileData]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $formState) {

    $storage = $formState->getStorage();
    if (!isset($storage['grantsProfileData'])) {
      $this->messenger()->addError($this->t('grantsProfileData not found!'));
      return;
    }

    $grantsProfileData = $storage['grantsProfileData'];

    $selectedCompanyArray = $this->grantsProfileService->getSelectedRoleData();
    $selectedCompany = $selectedCompanyArray['identifier'];

    $profileDataArray = $grantsProfileData->toArray();

    try {
      $success = $this->grantsProfileService->saveGrantsProfile($profileDataArray);
    }
    catch (\Throwable $e) {
      $success = FALSE;
      $this->logger('grants_profile')
        ->error('Grants profile saving failed. Error: @error', ['@error' => $e->getMessage()]);
    }
    $this->grantsProfileService->clearCache($selectedCompany);

    if ($success !== FALSE) {
      $this->messenger()
        ->addStatus($this->t('Grantsprofile for %c (%s) saved.', [
          '%c' => $selectedCompanyArray['name'],
          '%s' => $selectedCompany,
        ]));
    }

    $formState->setRedirect('grants_profile.show');
  }

  /**
   * Add address bits in separate method to improve readability.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Form state.
   * @param array|null $bankAccounts
   *   Current officials.
   * @param string|null $newItem
   *   New item.
   */
  public function addBankAccountBits(
    array &$form,
    FormStateInterface $formState,
    ?array $bankAccounts,
    ?string $newItem
  ) {
    $form['bankAccountWrapper'] = [
      '#type' => 'webform_section',
      '#title' => $this->t('Bank account numbers'),
      '#prefix' => '<div id="bankaccount-wrapper">',
      '#suffix' => '</div>',
    ];

    if (!$bankAccounts) {
      $bankAccounts = [];
    }

    $sessionHash = sha1(\Drupal::service('session')->getId());
    $uploadLocation = 'private://grants_profile/' . $sessionHash;

    $bankAccountValues = $formState->getValue('bankAccountWrapper') ?? $bankAccounts;
    unset($bankAccountValues['actions']);
    foreach ($bankAccountValues as $delta => $bankAccount) {
      if (array_key_exists('bank', $bankAccount) && !empty($bankAccount['bank'])) {
        $temp = $bankAccount['bank'];
        unset($bankAccountValues[$delta]['bank']);
        $bankAccountValues[$delta] = array_merge($bankAccountValues[$delta], $temp);
        $bankAccount = $bankAccount['bank'];
      }

      // Make sure we have proper UUID as address id.
      if (!$this->grantsProfileService->isValidUuid($bankAccount['bank_account_id'])) {
        $bankAccount['bank_account_id'] = Uuid::uuid4()->toString();
      }
      $nonEditable = FALSE;
      foreach ($bankAccounts as $profileAccount) {
        if ($bankAccount['bankAccount'] && self::accountsAreEqual($bankAccount['bankAccount'], $profileAccount['bankAccount'])) {
          $nonEditable = TRUE;
          break;
        }
      }
      $attributes = [];
      if ($nonEditable) {
        $attributes['readonly'] = 'readonly';
      }

      $confFilename = $bankAccount['confirmationFileName'] ?? $bankAccount['confirmationFile'];

      $form['bankAccountWrapper'][$delta]['bank'] = [

        '#type' => 'fieldset',
        '#title' => $this->t('Personal bank account'),
        'bankAccount' => [
          '#type' => 'textfield',
          '#title' => $this->t('Finnish bank account number in IBAN format'),
          '#default_value' => $bankAccount['bankAccount'],
          '#readonly' => $nonEditable,
          '#attributes' => $attributes,
        ],
        'confirmationFileName' => [
          '#title' => $this->t('Confirmation file'),
          '#type' => 'textfield',
          '#attributes' => ['readonly' => 'readonly'],
          '#default_value' => $confFilename,
        ],
        'confirmationFile' => [
          '#type' => 'managed_file',
          '#title' => $this->t("Attach a certificate of account access: bank's notification of the account owner or a copy of a bank statement."),
          '#multiple' => FALSE,
          '#uri_scheme' => 'private',
          '#file_extensions' => 'doc,docx,gif,jpg,jpeg,pdf,png,ppt,pptx,rtf,
        txt,xls,xlsx,zip',
          '#upload_validators' => [
            'file_validate_extensions' => [
              'doc docx gif jpg jpeg pdf png ppt pptx rtf txt xls xlsx zip',
            ],
          ],
          '#element_validate' => ['\Drupal\grants_profile\Form\GrantsProfileFormPrivatePerson::validateUpload'],
          '#upload_location' => $uploadLocation,
          '#sanitize' => TRUE,
          '#description' => $this->t('Only one file.<br>Limit: 32 MB.<br>
Allowed file types: doc, docx, gif, jpg, jpeg, pdf, png, ppt, pptx,
rtf, txt, xls, xlsx, zip.'),
          '#access' => $confFilename == NULL || is_array($confFilename),
        ],
        'bank_account_id' => [
          '#type' => 'hidden',
        ],
        'deleteButton' => [
          '#icon_left' => 'trash',
          '#type' => 'submit',
          '#value' => $this
            ->t('Delete'),
          '#name' => 'bankAccountWrapper--' . $delta,
          '#submit' => [
            '::removeOne',
          ],
          '#ajax' => [
            'callback' => '::addmoreCallback',
            'wrapper' => 'bankaccount-wrapper',
          ],
        ],
      ];
    }

    if ($newItem == 'bankAccountWrapper') {

      $form['bankAccountWrapper'][$delta + 1]['bank'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Personal bank account'),
        'bankAccount' => [
          '#type' => 'textfield',
          '#title' => $this->t('Finnish bank account number in IBAN format'),
        ],
        'confirmationFileName' => [
          '#type' => 'textfield',
          '#attributes' => ['readonly' => 'readonly'],
        ],
        'confirmationFile' => [
          '#type' => 'managed_file',
          '#title' => $this->t("Attach a certificate of account access: bank's notification of the account owner or a copy of a bank statement."),
          '#multiple' => FALSE,
          '#uri_scheme' => 'private',
          '#file_extensions' => 'doc,docx,gif,jpg,jpeg,pdf,png,ppt,pptx,rtf,
        txt,xls,xlsx,zip',
          '#upload_validators' => [
            'file_validate_extensions' => [
              'doc docx gif jpg jpeg pdf png ppt pptx rtf txt xls xlsx zip',
            ],
          ],
          '#element_validate' => ['\Drupal\grants_profile\Form\GrantsProfileFormPrivatePerson::validateUpload'],
          '#upload_location' => $uploadLocation,
          '#sanitize' => TRUE,
          '#description' => $this->t('Only one file.<br>Limit: 32 MB.<br>
Allowed file types: doc, docx, gif, jpg, jpeg, pdf, png, ppt, pptx,
rtf, txt, xls, xlsx, zip.'),
        ],
        'bank_account_id' => [
          '#type' => 'hidden',
        ],
        'deleteButton' => [
          '#type' => 'submit',
          '#icon_left' => 'trash',
          '#value' => $this
            ->t('Delete'),
          '#name' => 'bankAccountWrapper--' . ($delta + 1),
          '#submit' => [
            '::removeOne',
          ],
          '#ajax' => [
            'callback' => '::addmoreCallback',
            'wrapper' => 'bankaccount-wrapper',
          ],
        ],
      ];
      $formState->setValue('newItem', NULL);
    }

    $form['bankAccountWrapper']['actions']['add_bankaccount'] = [
      '#type' => 'submit',
      '#value' => $this
        ->t('Add bank account'),
      '#is_supplementary' => TRUE,
      '#icon_left' => 'plus-circle',
      '#name' => 'bankAccountWrapper--1',
      '#submit' => [
        '::addOne',
      ],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'bankaccount-wrapper',
      ],
      '#prefix' => '<div class="profile-add-more"">',
      '#suffix' => '</div>',
    ];
  }

  /**
   * Clean up form values.
   *
   * @param array $values
   *   Form values.
   * @param array $input
   *   User input.
   * @param array $storage
   *   Form storage.
   *
   * @return array
   *   Cleaned up Form Values.
   */
  public function cleanUpFormValues(array $values, array $input, array $storage): array {
    // Clean up empty values from form values.
    foreach ($values as $key => $value) {
      if (!is_array($value)) {
        continue;
      }
      if ($key == 'addressWrapper' && array_key_exists($key, $input)) {
        $values[$key] = [$value];
        unset($values[$key]['actions']);
        if (empty($value["address_id"])) {
          $values[$key][0]['address_id'] = Uuid::uuid4()
            ->toString();
        }
      }
      elseif ($key == 'bankAccountWrapper' && array_key_exists($key, $input)) {

        $values[$key] = $input[$key];
        unset($values[$key]['actions']);
        foreach ($value as $key2 => $loopItem) {
          // Get item from fieldset.
          $value2 = $loopItem['bank'];
          // Set value without fieldset.
          $values[$key][$key2] = $value2;
          // If we have added a new account,
          // then we need to create id for it.
          if (!array_key_exists('bank_account_id', $value2)) {
            $value2['bank_account_id'] = '';
          }
          if (!$this->grantsProfileService->isValidUuid($value2['bank_account_id'])) {
            $values[$key][$key2]['bank_account_id'] = Uuid::uuid4()
              ->toString();
          }

          if (isset($storage['confirmationFiles'][$key2])) {
            $values[$key][$key2]['confirmationFileName'] = $storage['confirmationFiles'][$key2]['filename'];
            $values[$key][$key2]['confirmationFile'] = $storage['confirmationFiles'][$key2]['filename'];
          }
          if (!empty($values[$key][$key2]['confirmationFileName'])) {
            $values[$key][$key2]['confirmationFile'] = $values[$key][$key2]['confirmationFileName'];
          }
        }
      }
    }
    unset($values['actions']);
    return $values;
  }

}
