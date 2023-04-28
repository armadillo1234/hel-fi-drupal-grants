<?php

namespace Drupal\grants_oma_asiointi\Plugin\Block;

use Drupal\helfi_atv\AtvDocumentNotFoundException;
use Drupal\helfi_helsinki_profiili\HelsinkiProfiiliUserData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\grants_handler\ApplicationHandler;
use Drupal\grants_profile\GrantsProfileService;
use Drupal\helfi_atv\AtvService;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "grants_oma_asiointi_block",
 *   admin_label = @Translation("Grants Oma Asiointi"),
 *   category = @Translation("Oma Asiointi")
 * )
 */
class OmaAsiointiBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The helfi_helsinki_profiili.userdata service.
   *
   * @var \Drupal\helfi_helsinki_profiili\HelsinkiProfiiliUserData
   */
  protected HelsinkiProfiiliUserData $helfiHelsinkiProfiiliUserdata;

  /**
   * The grants_profile.service service.
   *
   * @var \Drupal\grants_profile\GrantsProfileService
   */
  protected GrantsProfileService $grantsProfileService;

  /**
   * The grants_handler.application_handler service.
   *
   * @var \Drupal\grants_handler\ApplicationHandler
   */
  protected ApplicationHandler $applicationHandler;

  /**
   * The helfi_atv.atv_service service.
   *
   * @var \Drupal\helfi_atv\AtvService
   */
  protected AtvService $helfiAtvAtvService;

  /**
   * Current request.
   *
   * @var Symfony\Component\HttpFoundation\Request
   */
  protected Request $request;

  /**
   * Construct block object.
   *
   * @param array $configuration
   *   Block config.
   * @param string $plugin_id
   *   Plugin.
   * @param mixed $plugin_definition
   *   Plugin def.
   * @param \Drupal\helfi_helsinki_profiili\HelsinkiProfiiliUserData $helsinkiProfiiliUserData
   *   Helsinki profile user data.
   * @param \Drupal\grants_profile\GrantsProfileService $grants_profile_service
   *   The grants_profile.service service.
   * @param \Drupal\grants_handler\ApplicationHandler $grants_handler_application_handler
   *   The grants_handler.application_handler service.
   * @param \Drupal\helfi_atv\AtvService $helfi_atv_atv_service
   *   The helfi_atv.atv_service service.
   * @param Symfony\Component\HttpFoundation\Request $request
   *   Current request object.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    HelsinkiProfiiliUserData $helsinkiProfiiliUserData,
    GrantsProfileService $grants_profile_service,
    ApplicationHandler $grants_handler_application_handler,
    AtvService $helfi_atv_atv_service,
    Request $request
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->helfiHelsinkiProfiiliUserdata = $helsinkiProfiiliUserData;
    $this->grantsProfileService = $grants_profile_service;
    $this->applicationHandler = $grants_handler_application_handler;
    $this->helfiAtvAtvService = $helfi_atv_atv_service;
    $this->request = $request;
  }

  /**
   * Factory function.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container.
   * @param array $configuration
   *   Block config.
   * @param string $plugin_id
   *   Plugin.
   * @param mixed $plugin_definition
   *   Plugin def.
   *
   * @return static
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('helfi_helsinki_profiili.userdata'),
      $container->get('grants_profile.service'),
      $container->get('grants_handler.application_handler'),
      $container->get('helfi_atv.atv_service'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $selectedCompany = $this->grantsProfileService->getSelectedRoleData();
    $currentUser = \Drupal::currentUser();

    // If no company selected, no mandates no access.
    $roles = $currentUser->getRoles();
    if (
      in_array('helsinkiprofiili', $roles) &&
      $selectedCompany == NULL) {
      $build = [
        '#theme' => 'grants_oma_asiointi_block',
        '#hascompany' => FALSE,
      ];
      return $build;
      // Throw new CompanySelectException('User not authorised');.
    }

    $helsinkiProfileData = $this->helfiHelsinkiProfiiliUserdata->getUserProfileData();
    $appEnv = ApplicationHandler::getAppEnv();

    $messages = [];
    $submissions = [];

    try {
      $applicationDocuments = $this->helfiAtvAtvService->searchDocuments([
        'service' => 'AvustushakemusIntegraatio',
        'business_id' => $selectedCompany['identifier'],
        'lookfor' => 'appenv:' . $appEnv,
      ]);

      /**
       * Create rows for table.
       *
       * @var integer $key
       * @var  \Drupal\helfi_atv\AtvDocument $document
       */
      foreach ($applicationDocuments as $document) {
        if (
          str_contains($document->getTransactionId(), $appEnv) &&
          array_key_exists($document->getType(), ApplicationHandler::getApplicationTypes())
        ) {

          try {
            $submission = ApplicationHandler::submissionObjectFromApplicationNumber($document->getTransactionId(), $document);
            $submissionData = $submission->getData();
            $submissionMessages = ApplicationHandler::parseMessages($submissionData, TRUE);
            $messages += $submissionMessages;

            $ts = strtotime($submissionData['form_timestamp']);
            $submissions[$ts] = $submissionData;

          }
          catch (AtvDocumentNotFoundException $e) {
          }
        }
      }

    }
    catch (\Exception $e) {
    }

    $receivedMsgs = [];

    // Show only messages that are received from kasittelyjarjestelma.
    foreach ($messages as $message) {
      if ($message['sentBy'] === 'Avustusten kasittelyjarjestelma') {
        array_push($receivedMsgs, $message);
      }
    }

    $lang = \Drupal::languageManager()->getCurrentLanguage();
    krsort($submissions);
    krsort($messages);
    $link = Link::createFromRoute($this->t('Go to My Services'), 'grants_oma_asiointi.front');
    $allMessagesLink = Link::createFromRoute($this->t('See all messages'), 'grants_oma_asiointi.front');
    $build = [
      '#theme' => 'grants_oma_asiointi_block',
      '#allMessages' => $receivedMsgs,
      '#messages' => array_slice($receivedMsgs, 0, 2),
      '#allSubmissions' => $submissions,
      '#submissions' => array_slice($submissions, 0, 2),
      '#userProfileData' => $helsinkiProfileData['myProfile'],
      '#applicationTypes' => ApplicationHandler::getApplicationTypes(),
      '#lang' => $lang->getId(),
      '#link' => $link,
      '#allMessagesLink' => $allMessagesLink,
    ];

    return $build;
  }

  /**
   * Disable cache.
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
