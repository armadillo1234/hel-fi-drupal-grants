<?php

namespace Drupal\grants_profile\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\grants_profile\GrantsProfileService;
use Drupal\helfi_atv\AtvDocumentNotFoundException;
use Drupal\helfi_atv\AtvFailedToConnectException;
use GuzzleHttp\Exception\GuzzleException;
use PHP_IBAN\IBAN;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\grants_profile\TypedData\Definition\GrantsProfileRegisteredCommunityDefinition;
use Drupal\helfi_yjdh\Exception\YjdhException;

/**
 * Provides a Grants Profile form.
 */
class GrantsProfileFormRegisteredCommunity extends FormBase {

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
   * Constructs a new GrantsProfileForm object.
   *
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   Data manager.
   * @param \Drupal\grants_profile\GrantsProfileService $grantsProfileService
   *   Profile.
   */
  public function __construct(TypedDataManager $typed_data_manager, GrantsProfileService $grantsProfileService) {
    $this->typedDataManager = $typed_data_manager;
    $this->grantsProfileService = $grantsProfileService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): GrantsProfileFormRegisteredCommunity|static {
    return new static(
      $container->get('typed_data_manager'),
      $container->get('grants_profile.service')
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
  public function getFormId() {
    return 'grants_profile_registered_community';
  }

  /**
   * Get officials' roles.
   *
   * @return array
   *   Available roles.
   */
  public static function getOfficialRoles(): array {
    return [
      1 => t('Chairperson'),
      2 => t('Contact person'),
      3 => t('Other'),
      4 => t('Treasurer'),
      5 => t('Auditor'),
      7 => t('Secretary'),
      8 => t('Deputy chairperson'),
    ];
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

    $storage = $form_state->getStorage();
    $storage['profileDocument'] = $grantsProfile;

    // Use custom theme hook.
    $form['#theme'] = 'own_profile_form';
    $form['#tree'] = TRUE;

    $form['#after_build'] = ['Drupal\grants_profile\Form\GrantsProfileFormRegisteredCommunity::afterBuild'];

    $form['foundingYearWrapper'] = [
      '#type' => 'webform_section',
      '#title' => $this->t('Year of establishment'),
    ];
    $form['foundingYearWrapper']['foundingYear'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Year of establishment'),
      '#default_value' => $grantsProfileContent['foundingYear'],
    ];
    $form['foundingYearWrapper']['foundingYear']['#attributes']['class'][] = 'webform--small';

    $form['companyNameShortWrapper'] = [
      '#type' => 'webform_section',
      '#title' => $this->t('Abbreviated name'),
    ];
    $form['companyNameShortWrapper']['companyNameShort'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Abbreviated name'),
      '#default_value' => $grantsProfileContent['companyNameShort'],
    ];
    $form['companyNameShortWrapper']['companyNameShort']['#attributes']['class'][] = 'webform--large';

    $form['companyHomePageWrapper'] = [
      '#type' => 'webform_section',
      '#title' => $this->t('Website address'),
    ];
    $form['companyHomePageWrapper']['companyHomePage'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Website address'),
      '#default_value' => $grantsProfileContent['companyHomePage'],
    ];

    $form['businessPurposeWrapper'] = [
      '#type' => 'webform_section',
      '#title' => $this->t('Purpose of operations'),
    ];
    $form['businessPurposeWrapper']['businessPurpose'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description of the purpose of the activity of the registered association (max. 500 characters)'),
      '#default_value' => $grantsProfileContent['businessPurpose'],
      '#maxlength' => 500,
      '#required' => TRUE,
      '#counter_type' => 'character',
      '#counter_maximum' => 500,
      '#counter_minimum' => 1,
      '#counter_maximum_message' => '%d/500 merkkiä jäljellä',
      '#help' => $this->t('Briefly describe the purpose for which the community is working and how the community is fulfilling its purpose. For example, you can use the text "Community purpose and forms of action" in the Community rules. Please do not describe the purpose of the grant here, it will be asked later when completing the grant application.'),
    ];
    $form['businessPurposeWrapper']['businessPurpose']['#attributes']['class'][] = 'webform--large';

    $form['newItem'] = [
      '#type' => 'hidden',
      '#value' => NULL,
    ];
    $newItem = $form_state->getValue('newItem');

    $this->addAddressBits($form, $form_state, $grantsProfileContent['addresses'], $newItem);
    $this->addOfficialBits($form, $form_state, $grantsProfileContent['officials'], $newItem);
    $this->addbankAccountBits($form, $form_state, $grantsProfileContent['bankAccounts'], $newItem);

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save own information'),
    ];

    $form['#profilecontent'] = $grantsProfileContent;
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

    if ($fieldName == 'bankAccountWrapper') {
      $attachmentDeleteResults = self::deleteAttachmentFile($fieldValue[$deltaToRemove]['bank'], $formState);

      if ($attachmentDeleteResults) {
        \Drupal::messenger()
          ->addStatus('Bank account & verification attachment deleted.');

        // Remove item from items.
        unset($fieldValue[$deltaToRemove]);
        $formState->setValue($fieldName, $fieldValue);
        $formState->setRebuild();
      }
      else {
        \Drupal::messenger()
          ->addError('Attachment deletion failed, error has been logged. Please contact customer support');

        // Remove item from items.
        unset($fieldValue[$deltaToRemove]);
        $formState->setValue($fieldName, $fieldValue);
        $formState->setRebuild();

      }
    }
    else {
      // Remove item from items.
      unset($fieldValue[$deltaToRemove]);
      $formState->setValue($fieldName, $fieldValue);
      $formState->setRebuild();
    }

  }

  /**
   * Ajax callback.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Forms state.
   *
   * @return mixed
   *   Form element for replacing.
   */
  public static function addmoreCallback(array &$form, FormStateInterface $formState): mixed {

    $triggeringElement = $formState->getTriggeringElement();
    [
      $fieldName,
    ] = explode('--', $triggeringElement['#name']);

    return $form[$fieldName];
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

    if (array_key_exists('officialWrapper', $input)) {
      $values["officialWrapper"] = $input["officialWrapper"];
    }

    if (array_key_exists('bankAccountWrapper', $input)) {
      $values["bankAccountWrapper"] = $input["bankAccountWrapper"];
    }

    $values = $this->cleanUpFormValues($values, $input, $storage);

    // Set clean values to form state.
    $formState->setValues($values);

    if (array_key_exists('addressWrapper', $values)) {
      unset($values["addressWrapper"]["actions"]);
      $grantsProfileContent['addresses'] = $values["addressWrapper"];
    }

    if (array_key_exists('officialWrapper', $values)) {
      unset($values["officialWrapper"]["actions"]);
      $grantsProfileContent['officials'] = $values["officialWrapper"];
    }

    if (array_key_exists('bankAccountWrapper', $values)) {
      unset($values["bankAccountWrapper"]["actions"]);
      $grantsProfileContent['bankAccounts'] = $values["bankAccountWrapper"];
    }

    $grantsProfileContent["foundingYear"] = $values["foundingYearWrapper"]["foundingYear"];
    $grantsProfileContent["companyNameShort"] = $values["companyNameShortWrapper"]["companyNameShort"];
    $grantsProfileContent["companyHomePage"] = $values["companyHomePageWrapper"]["companyHomePage"];
    $grantsProfileContent["businessPurpose"] = $values["businessPurposeWrapper"]["businessPurpose"];

    $this->validateBankAccounts($values, $formState);
    $this->validateOfficials($values, $formState);

    parent::validateForm($form, $formState);

    $grantsProfileDefinition = GrantsProfileRegisteredCommunityDefinition::create('grants_profile_registered_community');
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

        if ($propertyPathArray[0] == 'companyNameShort') {
          $propertyPath = 'companyNameShortWrapper][companyNameShort';
        }
        elseif ($propertyPathArray[0] == 'companyHomePage') {
          $propertyPath = 'companyHomePageWrapper][companyHomePage';
        }
        elseif ($propertyPathArray[0] == 'businessPurpose') {
          $propertyPath = 'businessPurposeWrapper][businessPurpose';
        }
        elseif ($propertyPathArray[0] == 'foundingYear') {
          $propertyPath = 'foundingYearWrapper][foundingYear';
        }
        elseif ($propertyPathArray[0] == 'addresses') {
          if (count($propertyPathArray) == 1) {
            $errorElement = $form["addressWrapper"];
            $errorMesg = 'You must add one address';
          }
          else {
            $propertyPath = 'addressWrapper][' . $propertyPathArray[1] . '][address][' . $propertyPathArray[2];
          }
        }
        elseif ($propertyPathArray[0] == 'bankAccounts') {
          if (count($propertyPathArray) == 1) {
            $errorElement = $form["bankAccountWrapper"];
            $errorMesg = 'You must add one bank account';
          }
          else {
            $propertyPath = 'bankAccountWrapper][' . $propertyPathArray[1] . '][bank][' . $propertyPathArray[2];
          }

        }
        elseif (count($propertyPathArray) > 1 && $propertyPathArray[0] == 'officials') {
          $propertyPath = 'officialWrapper][' . $propertyPathArray[1] . '][official][' . $propertyPathArray[2];
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

    /** @var \Drupal\grants_profile\GrantsProfileService $grantsProfileService */
    $grantsProfileService = \Drupal::service('grants_profile.service');
    $selectedRoleData = $grantsProfileService->getSelectedRoleData();
    $selectedCompany = $selectedRoleData['identifier'];

    $profileDataArray = $grantsProfileData->toArray();

    try {
      $success = $grantsProfileService->saveGrantsProfile($profileDataArray);
    }
    catch (\Exception $e) {
      $success = FALSE;
      $this->logger('grants_profile')
        ->error('Grants profile saving failed. Error: @error', ['@error' => $e->getMessage()]);
    }
    catch (GuzzleException $e) {
      $success = FALSE;
      $this->logger('grants_profile')
        ->error('Grants profile saving failed. Error: @error', ['@error' => $e->getMessage()]);
    }
    $grantsProfileService->clearCache($selectedCompany);

    if ($success !== FALSE) {
      $this->messenger()
        ->addStatus($this->t('Grantsprofile for %c (%s) saved.', [
          '%c' => $selectedRoleData['name'],
          '%s' => $selectedCompany,
        ]));
    }

    $formState->setRedirect('grants_profile.show');
  }

  /**
   * Create new profile object.
   *
   * @param \Drupal\grants_profile\GrantsProfileService $grantsProfileService
   *   Profile service.
   * @param mixed $selectedCompany
   *   Customers' selected company.
   * @param array $form
   *   Form array.
   *
   * @return array
   *   New profle.
   */
  public function createNewProfile(
    GrantsProfileService $grantsProfileService,
    mixed $selectedCompany,
    array $form
  ): array {

    try {
      // Initialize a new one.
      // This fetches company details from yrtti / ytj.
      $grantsProfileContent = $grantsProfileService->initGrantsProfileRegisteredCommunity($selectedCompany, []);

      // Initial save of the new profile so we can add files to it.
      $newProfile = $grantsProfileService->saveGrantsProfile($grantsProfileContent);
    }
    catch (YjdhException $e) {
      $newProfile = NULL;
      // If no company data is found, we cannot continue.
      $this->messenger()
        ->addError($this->t('Community details not found in registries. Please contact customer service'));
      $this->logger(
        'grants_profile')
        ->error('Error fetching community data. Error: %error', [
          '%error' => $e->getMessage(),
        ]
            );
      $form['#disabled'] = TRUE;
    }
    catch (AtvDocumentNotFoundException | AtvFailedToConnectException | GuzzleException $e) {
      $newProfile = NULL;
      // If no company data is found, we cannot continue.
      $this->messenger()
        ->addError($this->t('Community details not found in registries. Please contact customer service'));
      $this->logger(
        'grants_profile')
        ->error('Error fetching community data. Error: %error', [
          '%error' => $e->getMessage(),
        ]
            );
    }
    return [$newProfile, $form];
  }

  /**
   * Add address bits in separate method to improve readability.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Form state.
   * @param array $addresses
   *   Current addresses.
   * @param string|null $newItem
   *   New item title.
   */
  public function addAddressBits(
    array &$form,
    FormStateInterface $formState,
    array $addresses,
    ?string $newItem
  ) {

    $form['addressWrapper'] = [
      '#type' => 'webform_section',
      '#title' => $this->t('Addresses'),
      '#prefix' => '<div id="addresses-wrapper">',
      '#suffix' => '</div>',
    ];

    $addressValues = $formState->getValue('addressWrapper') ?? $addresses;
    unset($addressValues['actions']);
    foreach (array_values($addressValues) as $delta => $address) {
      if (array_key_exists('address', $address)) {
        $temp = $address['address'];
        unset($address['address']);
        $addressValues[$delta] = array_merge($address, $temp);
      }
      // Make sure we have proper UUID as address id.
      if (!$this->grantsProfileService->isValidUuid($address['address_id'])) {
        $address['address_id'] = Uuid::uuid4()->toString();
      }

      $form['addressWrapper'][$delta]['address'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Community address'),
      ];
      $form['addressWrapper'][$delta]['address']['street'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Street address'),
        '#default_value' => $address['street'],
      ];
      $form['addressWrapper'][$delta]['address']['postCode'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Postal code'),
        '#default_value' => $address['postCode'],
      ];
      $form['addressWrapper'][$delta]['address']['city'] = [
        '#type' => 'textfield',
        '#title' => $this->t('City/town', [], ['context' => 'Profile Address']),
        '#default_value' => $address['city'],
      ];
      $form['addressWrapper'][$delta]['address']['country'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Country'),
        '#default_value' => $address['country'],
      ];

      // We need the delta / id to create delete links in element.
      $form['addressWrapper'][$delta]['address']['address_id'] = [
        '#type' => 'hidden',
        '#value' => $address['address_id'],
      ];
      // Address delta is replaced with alter hook in module file.
      $form['addressWrapper'][$delta]['address']['deleteButton'] = [
        '#type' => 'submit',
        '#icon_left' => 'trash',
        '#value' => $this
          ->t('Delete'),
        '#name' => 'addressWrapper--' . $delta,
        '#submit' => [
          '::removeOne',
        ],
        '#ajax' => [
          'callback' => '::addmoreCallback',
          'wrapper' => 'addresses-wrapper',
        ],
      ];
    }

    if ($newItem == 'addressWrapper') {

      $form['addressWrapper'][] = [
        'address' => [
          '#type' => 'fieldset',
          '#title' => $this->t('Community address'),
          'street' => [
            '#type' => 'textfield',
            '#title' => $this->t('Street address'),
          ],
          'postCode' => [
            '#type' => 'textfield',
            '#title' => $this->t('Postal code'),
          ],
          'city' => [
            '#type' => 'textfield',
            '#title' => $this->t('City/town', [], ['context' => 'Profile Address']),
          ],
          // We need the delta / id to create delete links in element.
          'address_id' => [
            '#type' => 'hidden',
            '#value' => Uuid::uuid4()->toString(),
          ],
          // Address delta is replaced with alter hook in module file.
          'deleteButton' => [
            '#type' => 'submit',
            '#icon_left' => 'trash',
            '#value' => $this
              ->t('Delete'),
            '#name' => 'addressWrapper--' . ($delta + 1),
            '#submit' => [
              '::removeOne',
            ],
            '#ajax' => [
              'callback' => '::addmoreCallback',
              'wrapper' => 'addresses-wrapper',
            ],
          ],
        ],
      ];
      $formState->setValue('newItem', NULL);
    }

    $form['addressWrapper']['actions']['add_address'] = [
      '#type' => 'submit',
      '#value' => $this
        ->t('Add address'),
      '#name' => 'addressWrapper--1',
      '#is_supplementary' => TRUE,
      '#icon_left' => 'plus-circle',
      '#submit' => [
        '::addOne',
      ],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'addresses-wrapper',
      ],
      '#prefix' => '<div class="profile-add-more"">',
      '#suffix' => '</div>',
    ];
  }

  /**
   * Add official bits in separate method to improve readability.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Form state.
   * @param array $officials
   *   Current officials.
   * @param string|null $newItem
   *   Name of new item.
   */
  public function addOfficialBits(
    array &$form,
    FormStateInterface $formState,
    array $officials,
    ?string $newItem
  ) {
    $form['officialWrapper'] = [
      '#type' => 'webform_section',
      '#title' => $this->t('Persons responsible for operations'),
      '#prefix' => '<div id="officials-wrapper">',
      '#suffix' => '</div>',
    ];

    $roles = [
      0 => $this->t('Select'),
    ] + self::getOfficialRoles();

    $officialValues = $formState->getValue('officialWrapper') ?? $officials;
    unset($officialValues['actions']);
    foreach ($officialValues as $delta => $official) {

      // Make sure we have proper UUID as address id.
      if (!$this->grantsProfileService->isValidUuid($official['official_id'])) {
        $official['official_id'] = Uuid::uuid4()->toString();
      }

      $form['officialWrapper'][$delta]['official'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Community official'),
        'name' => [
          '#type' => 'textfield',
          '#title' => $this->t('Name'),
          '#default_value' => $official['name'],
        ],
        'role' => [
          '#type' => 'select',
          '#options' => $roles,
          '#title' => $this->t('Role'),
          '#default_value' => $official['role'],
        ],
        'email' => [
          '#type' => 'textfield',
          '#title' => $this->t('Email address'),
          '#default_value' => $official['email'],
        ],
        'phone' => [
          '#type' => 'textfield',
          '#title' => $this->t('Telephone'),
          '#default_value' => $official['phone'],
        ],
        'official_id' => [
          '#type' => 'hidden',
          '#default_value' => $official['official_id'],
        ],
        'deleteButton' => [
          '#type' => 'submit',
          '#icon_left' => 'trash',
          '#value' => $this
            ->t('Delete'),
          '#name' => 'officialWrapper--' . $delta,
          '#submit' => [
            '::removeOne',
          ],
          '#ajax' => [
            'callback' => '::addmoreCallback',
            'wrapper' => 'officials-wrapper',
          ],
        ],
      ];
    }

    if ($newItem == 'officialWrapper') {

      $form['officialWrapper'][$delta + 1]['official'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Community official'),
        'name' => [
          '#type' => 'textfield',
          '#title' => $this->t('Name'),
        ],
        'role' => [
          '#type' => 'select',
          '#options' => $roles,
          '#title' => $this->t('Role'),
        ],
        'email' => [
          '#type' => 'textfield',
          '#title' => $this->t('Email address'),
        ],
        'phone' => [
          '#type' => 'textfield',
          '#title' => $this->t('Telephone'),
        ],
        'official_id' => [
          '#type' => 'hidden',
          '#value' => Uuid::uuid4()->toString(),
        ],
        'deleteButton' => [
          '#type' => 'submit',
          '#icon_left' => 'trash',
          '#value' => $this
            ->t('Delete'),
          '#name' => 'officialWrapper--' . $delta,
          '#submit' => [
            '::removeOne',
          ],
          '#ajax' => [
            'callback' => '::addmoreCallback',
            'wrapper' => 'officials-wrapper',
          ],
        ],
      ];
      $formState->setValue('newItem', NULL);
    }

    $form['officialWrapper']['actions']['add_official'] = [
      '#type' => 'submit',
      '#value' => $this
        ->t('Add official'),
      '#is_supplementary' => TRUE,
      '#icon_left' => 'plus-circle',
      '#name' => 'officialWrapper--1',
      '#submit' => [
        '::addOne',
      ],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'officials-wrapper',
      ],
      '#prefix' => '<div class="profile-add-more"">',
      '#suffix' => '</div>',
    ];
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
      }

      // Make sure we have proper UUID as address id.
      if (!$this->grantsProfileService->isValidUuid($bankAccount['bank_account_id'])) {
        $bankAccount['bank_account_id'] = Uuid::uuid4()->toString();
      }

      $confFilename = $bankAccount['confirmationFileName'] ?? $bankAccount['confirmationFile'];

      $form['bankAccountWrapper'][$delta]['bank'] = [

        '#type' => 'fieldset',
        '#title' => $this->t('Community bank account'),
        'bankAccount' => [
          '#type' => 'textfield',
          '#title' => $this->t('Finnish bank account number in IBAN format'),
          '#default_value' => $bankAccount['bankAccount'],
          '#readonly' => TRUE,
          '#attributes' => [
            'readonly' => 'readonly',
          ],
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
          '#element_validate' => ['\Drupal\grants_profile\Form\GrantsProfileFormRegisteredCommunity::validateUpload'],
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
        '#title' => $this->t('Community bank account'),
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
          '#element_validate' => ['\Drupal\grants_profile\Form\GrantsProfileFormRegisteredCommunity::validateUpload'],
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
        $values[$key] = $input[$key];
        unset($values[$key]['actions']);
        foreach ($value as $key2 => $value2) {
          if (empty($value2["address_id"])) {
            $values[$key][$key2]['address_id'] = Uuid::uuid4()
              ->toString();
          }
          if (array_key_exists('address', $value2) && !empty($value2['address'])) {
            $temp = $value2['address'];
            unset($values[$key][$key2]['address']);
            $values[$key][$key2] = array_merge($values[$key][$key2], $temp);
          }
        }
      }
      elseif ($key == 'officialWrapper' && array_key_exists($key, $input)) {
        $values[$key] = $input[$key];
        unset($values[$key]['actions']);
        foreach ($value as $key2 => $value2) {

          if (empty($value2["official_id"])) {
            $values[$key][$key2]['official_id'] = Uuid::uuid4()
              ->toString();
          }
          if (array_key_exists('official', $value2) && !empty($value2['official'])) {
            $temp = $value2['official'];
            unset($values[$key][$key2]['official']);
            $values[$key][$key2] = array_merge($values[$key][$key2], $temp);
          }
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
    return $values;
  }

  /**
   * Validate bank accounts.
   *
   * To reduce complexity.
   *
   * @param array $values
   *   Form values.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Form state.
   */
  public function validateBankAccounts(array $values, FormStateInterface $formState): void {
    if (array_key_exists('bankAccountWrapper', $values)) {

      if (empty($values["bankAccountWrapper"])) {
        $elementName = 'bankAccountWrapper]';
        $formState->setErrorByName($elementName, $this->t('You must add one bank account'));
        return;
      }

      foreach ($values["bankAccountWrapper"] as $key => $accountData) {

        if (!empty($accountData['bankAccount'])) {
          $myIban = new IBAN($accountData['bankAccount']);
          $ibanValid = FALSE;

          if ($myIban->Verify()) {
            // Get the country part from an IBAN.
            $iban_country = $myIban->Country();
            // Only allow Finnish IBAN account numbers..
            if ($iban_country == 'FI') {
              // If so, return true.
              $ibanValid = TRUE;
            }
          }
          if (!$ibanValid) {
            $elementName = 'bankAccountWrapper][' . $key . '][bank][bankAccount';
            $formState->setErrorByName($elementName, $this->t('Not valid Finnish IBAN: @iban', ['@iban' => $accountData["bankAccount"]]));
          }
        }
        else {
          $elementName = 'bankAccountWrapper][' . $key . '][bank][bankAccount';
          $formState->setErrorByName($elementName, $this->t('You must enter valid Finnish iban'));
        }
        if ((empty($accountData["confirmationFileName"]) && empty($accountData["confirmationFile"]['fids']))) {
          $elementName = 'bankAccountWrapper][' . $key . '][bank][confirmationFile';
          $formState->setErrorByName($elementName, $this->t('You must add confirmation file for account: @iban', ['@iban' => $accountData["bankAccount"]]));
        }
      }
    }
  }

  /**
   * Validate officials.
   *
   * To reduce complexity.
   *
   * @param array $values
   *   Form values.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Form state.
   */
  public function validateOfficials(array $values, FormStateInterface $formState): void {
    if (array_key_exists('officialWrapper', $values)) {

      if (empty($values["officialWrapper"])) {
        $elementName = 'officialWrapper]';
        $formState->setErrorByName($elementName, $this->t('You must add one official'));
        return;
      }

      foreach ($values["officialWrapper"] as $key => $official) {

        if ((empty($official["role"]) || $official["role"] == 0)) {
          $elementName = 'officialWrapper][' . $key . '][official][role';
          $formState->setErrorByName($elementName, $this->t('You must select a role for official'));
        }

      }
    }
  }

  /**
   * Delete given attachment from ATV.
   *
   * @param array $fieldValue
   *   Field contents.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Form state object.
   *
   * @return bool
   *   Result of deletion.
   */
  public static function deleteAttachmentFile(array $fieldValue, FormStateInterface $formState): bool {
    $fieldToRemove = $fieldValue;

    $storage = $formState->getStorage();
    /** @var \Drupal\helfi_atv\AtvDocument $grantsProfileDocument */
    $grantsProfileDocument = $storage['profileDocument'];

    // Try to look for a attachment from document.
    $attachmentToDelete = array_filter(
      $grantsProfileDocument->getAttachments(),
      function ($item) use ($fieldToRemove) {
        if ($item['filename'] == $fieldToRemove['confirmationFileName']) {
          return TRUE;
        }
        return FALSE;
      });

    $attachmentToDelete = reset($attachmentToDelete);
    $hrefToDelete = NULL;

    // If attachment is found.
    if ($attachmentToDelete) {
      // Get href for deletion.
      $hrefToDelete = $attachmentToDelete['href'];
    }
    else {
      // Attachment not found, so we must have just added one.
      $triggeringElement = $formState->getTriggeringElement();
      // Get delta for deleting.
      [$fieldName, $delta] = explode('--', $triggeringElement["#name"]);
      // Upload function has added the attachment information earlier.
      if ($justAddedElement = $storage["confirmationFiles"][(int) $delta]) {
        // So we can just grab that href and delete it from ATV.
        $hrefToDelete = $justAddedElement["href"];
      }
    }

    if (!$hrefToDelete) {
      return FALSE;
    }

    /** @var \Drupal\helfi_atv\AtvService $atvService */
    $atvService = \Drupal::service('helfi_atv.atv_service');
    /** @var \Drupal\helfi_audit_log\AuditLogService $auditLogService */
    $auditLogService = \Drupal::service('helfi_audit_log.audit_log');

    try {
      // Delete attachment by href.
      $deleteResult = $atvService->deleteAttachmentByUrl($hrefToDelete);

      $message = [
        "operation" => "GRANTS_APPLICATION_ATTACHMENT_DELETE",
        "status" => "SUCCESS",
        "target" => [
          "id" => $grantsProfileDocument->getId(),
          "type" => $grantsProfileDocument->getType(),
          "name" => $grantsProfileDocument->getTransactionId(),
        ],
      ];
      $auditLogService->dispatchEvent($message);

    }
    catch (\Throwable $e) {

      $deleteResult = FALSE;

      $message = [
        "operation" => "GRANTS_APPLICATION_ATTACHMENT_DELETE",
        "status" => "FAILURE",
        "target" => [
          "id" => $grantsProfileDocument->getId(),
          "type" => $grantsProfileDocument->getType(),
          "name" => $grantsProfileDocument->getTransactionId(),
        ],
      ];
      $auditLogService->dispatchEvent($message);

      \Drupal::logger('grants_profile')
        ->error('Attachment deletion failed, @error', ['@error' => $e->getMessage()]);
    }

    return $deleteResult;
  }

  /**
   * Handle possible errors after form is built.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Form state.
   *
   * @return array
   *   Updated form.
   */
  public static function afterBuild(array $form, FormStateInterface &$formState): array {

    $formErrors = $formState->getErrors();

    return $form;
  }

}
