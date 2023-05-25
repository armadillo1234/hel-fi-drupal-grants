<?php

namespace Drupal\grants_handler\Form;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\grants_attachments\AttachmentHandler;
use Drupal\grants_attachments\AttachmentRemover;
use Drupal\grants_handler\ApplicationHandler;
use Drupal\grants_handler\MessageService;
use Drupal\helfi_atv\AtvService;
use Drupal\webform\Entity\WebformSubmission;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Grants Handler form.
 */
class MessageForm extends FormBase {

  /**
   * Drupal\Core\TypedData\TypedDataManager definition.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected TypedDataManager $typedDataManager;

  /**
   * Communicate messages to integration.
   *
   * @var \Drupal\grants_handler\MessageService
   */
  protected MessageService $messageService;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Handle application tasks.
   *
   * @var \Drupal\grants_handler\ApplicationHandler
   */
  protected ApplicationHandler $applicationHandler;

  /**
   * Access ATV.
   *
   * @var \Drupal\helfi_atv\AtvService
   */
  protected AtvService $atvService;

  /**
   * Remove attachment files.
   *
   * @var \Drupal\grants_attachments\AttachmentRemover
   */
  protected AttachmentRemover $attachmentRemover;

  /**
   * Print / log debug things.
   *
   * @var bool
   */
  protected bool $debug;

  /**
   * Constructs a new AddressForm object.
   */
  public function __construct(
    TypedDataManager $typed_data_manager,
    MessageService $messageService,
    EntityTypeManager $entityTypeManager,
    ApplicationHandler $applicationHandler,
    AtvService $atvService,
    AttachmentRemover $attachmentRemover
  ) {
    $this->typedDataManager = $typed_data_manager;
    $this->messageService = $messageService;
    $this->entityTypeManager = $entityTypeManager;
    $this->applicationHandler = $applicationHandler;
    $this->atvService = $atvService;
    $this->attachmentRemover = $attachmentRemover;

    $debug = getenv('debug');

    if ($debug == 'true') {
      $this->debug = TRUE;
    }
    else {
      $this->debug = FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): MessageForm|static {
    return new static(
      $container->get('typed_data_manager'),
      $container->get('grants_handler.message_service'),
      $container->get('entity_type.manager'),
      $container->get('grants_handler.application_handler'),
      $container->get('helfi_atv.atv_service'),
      $container->get('grants_attachments.attachment_remover'),

    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'grants_handler_message';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, WebformSubmission $webform_submission = NULL) {

    $storage = $form_state->getStorage();
    $storage['webformSubmission'] = $webform_submission;

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#required' => TRUE,
    ];

    $sessionHash = sha1(\Drupal::service('session')->getId());
    $upload_location = 'private://grants_messages/' . $sessionHash;

    $maxFileSizeInBytes = (1024 * 1024) * 32;

    $form['messageAttachment'] = [
      '#type' => 'managed_file',
      '#title' => t('Attachment'),
      '#multiple' => FALSE,
      '#uri_scheme' => 'private',
      '#file_extensions' => 'doc,docx,gif,jpg,jpeg,pdf,png,ppt,pptx,rtf,txt,xls,xlsx,zip',
      '#upload_validators' => [
        'file_validate_extensions' => ['doc docx gif jpg jpeg pdf png ppt pptx rtf txt xls xlsx zip'],
        'file_validate_size' => [$maxFileSizeInBytes],
      ],
      '#description' => $this->t('Only one file.<br>Limit: 32 MB.<br>
Allowed file types: doc, docx, gif, jpg, jpeg, pdf, png, ppt, pptx,
rtf, txt, xls, xlsx, zip.'),
      '#element_validate' => ['\Drupal\grants_handler\Form\MessageForm::validateUpload'],
      '#upload_location' => $upload_location,
      '#sanitize' => TRUE,
      '#description' => $this->t('Add attachment to your message'),
    ];
    $form['attachmentDescription'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Attachment description'),
      '#required' => FALSE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
    ];

    $form_state->setStorage($storage);

    return $form;
  }

  /**
   * Validate & upload file attachment.
   *
   * This is done here because we want to show upload errors inline with the
   * form element. And only way to check upload is to actually do the upload,
   * ATV will error and we will respond accordingly.
   *
   * @param array $element
   *   Element tobe validated.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Form state.
   * @param array $form
   *   The form.
   */
  public static function validateUpload(
    array &$element,
    FormStateInterface $formState,
    array &$form
  ): void {

    $triggeringElement = $formState->getTriggeringElement();

    if (!str_contains($triggeringElement["#name"], 'messageAttachment_upload_button')) {
      return;
    }

    $storage = $formState->getStorage();
    $webformSubmission = $storage['webformSubmission'];
    $webformData = $webformSubmission->getData();
    $applicationNumber = $webformData['application_number'];

    /** @var \Drupal\helfi_atv\AtvService $atvService */
    $atvService = \Drupal::service('helfi_atv.atv_service');

    /** @var \Drupal\grants_handler\ApplicationHandler $applicationHandler */
    $applicationHandler = \Drupal::service('grants_handler.application_handler');

    try {
      $applicationDocument = $applicationHandler->getAtvDocument($applicationNumber);

      /** @var \Drupal\file\Entity\File $file */
      foreach ($element["#files"] as $file) {

        // Upload attachment to document.
        $attachmentResponse = $atvService->uploadAttachment(
          $applicationDocument->getId(),
          $file->getFilename(),
          $file
        );

        if ($attachmentResponse) {
          $storage['messageAttachment'] = [
            'file' => $file,
            'response' => $attachmentResponse,
          ];
        }
      }
    }
    catch (\Throwable $e) {
      // Set error to form.
      $formState->setError($element, 'File upload failed, error has been logged.');
      // Log error.
      \Drupal::logger('message_form')
        ->error('Message upload failed, error: @error',
          ['@error' => $e->getMessage()]
            );
    }

    $formState->setStorage($storage);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Ajax callback. Not used currently.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   State.
   */
  public function ajaxCallback(array $form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $storage = $form_state->getStorage();
    if (!isset($storage['webformSubmission'])) {
      $this->messenger()->addError($this->t('webformSubmission not found!'));
      return;
    }

    /** @var \Drupal\webform\Entity\WebformSubmission $submission */
    $submission = $storage['webformSubmission'];
    $submissionData = $submission->getData();

    $nextMessageId = Uuid::uuid4()->toString();

    $attachment = $storage['messageAttachment'] ?? [];
    $data = [
      'body' => Xss::filter($form_state->getValue('message')),
      'messageId' => $nextMessageId,
    ];

    if (!empty($attachment)) {
      /** @var \Drupal\grants_attachments\AttachmentRemover $attachmentRemover */
      $attachmentRemover = \Drupal::service('grants_attachments.attachment_remover');

      $response = $attachment['response'];
      $file = $attachment['file'];

      $integrationId = AttachmentHandler::getIntegrationIdFromFileHref($response['href']);
      $integrationId = AttachmentHandler::addEnvToIntegrationId($integrationId);

      $data['attachments'] = [
        (object) [
          'fileName' => $response['filename'],
          'description' => $form_state->getValue('attachmentDescription'),
          'integrationID' => $integrationId,
        ],
      ];

      // Remove file attachment directly after upload.
      $attachmentRemover->removeGrantAttachments(
        [$file->id()],
        [$file->id() => ['upload' => TRUE]],
        $submissionData['application_number'],
        getenv('DEBUG'),
        $submission->id()
      );
    }

    if ($this->messageService->sendMessage($data, $submission, $nextMessageId)) {
      $this->messenger()
        ->addStatus($this->t('Your message has been sent. Please note that it will take some time it appears on application.'));
      $this->messenger()
        ->addStatus($this->t('Your message: @message', ['@message' => $data['body']]));
    }
    else {
      $this->messenger()
        ->addStatus($this->t('Sending of your message failed.'));
    }
  }

}
