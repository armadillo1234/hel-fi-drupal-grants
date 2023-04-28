<?php

namespace Drupal\grants_mandate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\grants_mandate\GrantsMandateService;
use Drupal\grants_profile\GrantsProfileService;
use Drupal\helfi_helsinki_profiili\HelsinkiProfiiliUserData;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Grants Profile form.
 */
class ApplicantMandateForm extends FormBase {

  /**
   * Access to profile data.
   *
   * @var \Drupal\grants_profile\GrantsProfileService
   */
  protected GrantsProfileService $grantsProfileService;

  /**
   * Access to helsinki profile data.
   *
   * @var \Drupal\helfi_helsinki_profiili\HelsinkiProfiiliUserData
   */
  protected HelsinkiProfiiliUserData $helsinkiProfiiliUserData;

  /**
   * Use mandate service things.
   *
   * @var \Drupal\grants_mandate\GrantsMandateService
   */
  protected GrantsMandateService $grantsMandateService;

  /**
   * Constructs a new ModalAddressForm object.
   */
  public function __construct(
    GrantsProfileService $grantsProfileService,
    HelsinkiProfiiliUserData $helsinkiProfiiliUserData,
    GrantsMandateService $grantsMandateService
  ) {
    $this->grantsProfileService = $grantsProfileService;
    $this->helsinkiProfiiliUserData = $helsinkiProfiiliUserData;
    $this->grantsMandateService = $grantsMandateService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ApplicantMandateForm|static {
    return new static(
      $container->get('grants_profile.service'),
      $container->get('helfi_helsinki_profiili.userdata'),
      $container->get('grants_mandate.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'grants_mandate_type';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $userData = $this->helsinkiProfiiliUserData->getUserData();

    $profileOptions = [
      'new' => $this->t('Add new Unregistered community'),
    ];
    $profiles = [];
    try {
      $profiles = $this->grantsProfileService->getUsersGrantsProfiles($userData['sub'], 'unregistered_community');

      /** @var \Drupal\helfi_atv\AtvDocument $profile */
      foreach ($profiles as $profile) {
        $meta = $profile->getMetadata();
        $content = $profile->getContent();
        $profileOptions[$meta["profile_id"]] = $content['companyName'];
      }

    }
    catch (\Throwable $e) {
    }

    $form_state->setStorage([
      'userCommunities' => $profiles,
    ]);

    $form['info'] = [
      '#markup' => '<p>' . $this->t('Choose the applicant role you want to use for the application') . '</p>',
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['registered_community'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['hds-card__body']],
      '#prefix' => '<div class="hds-card hds-card--applicant-role">',
      '#suffix' => '</div>',
    ];
    $form['actions']['registered_community']['info'] = [
      '#theme' => 'select_applicant_role',
      '#icon' => 'group',
      '#role' => $this->t('Registered community'),
      '#role_description' => $this->t('This is a short description of the applicant role.'),
    ];
    $form['actions']['registered_community']['submit'] = [
      '#type' => 'submit',
      '#name' => 'registered_community',
      '#value' => $this->t('Select Registered community role & authorize mandate'),
    ];
    $form['actions']['unregistered_community'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['hds-card__body']],
      '#prefix' => '<div class="hds-card hds-card--applicant-role">',
      '#suffix' => '</div>',
    ];
    $form['actions']['unregistered_community']['info'] = [
      '#theme' => 'select_applicant_role',
      '#icon' => 'group',
      '#role' => $this->t('Unregistered community'),
      '#role_description' => $this->t('This is a short description of the applicant role.'),
    ];

    $form['actions']['unregistered_community']['unregistered_community_selection'] = [
      '#type' => 'select',
      '#options' => $profileOptions,
    ];

    $form['actions']['unregistered_community']['submit'] = [
      '#type' => 'submit',
      '#name' => 'unregistered_community',
      '#value' => $this->t('Select Unregistered community role'),
    ];
    $form['actions']['private_person'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['hds-card__body']],
      '#prefix' => '<div class="hds-card hds-card--applicant-role">',
      '#suffix' => '</div>',
    ];
    $form['actions']['private_person']['info'] = [
      '#theme' => 'select_applicant_role',
      '#icon' => 'group',
      '#role' => $this->t('Private person'),
      '#role_description' => $this->t('This is a short description of the applicant role.'),
    ];
    $form['actions']['private_person']['submit'] = [
      '#name' => 'private_person',
      '#type' => 'submit',
      '#value' => $this->t('Select Private person role'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\helfi_helsinki_profiili\TokenExpiredException
   * @throws \Drupal\grants_mandate\GrantsMandateException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $triggeringElement = $form_state->getTriggeringElement();

    $selectedType = $triggeringElement['#name'];
    $this->grantsProfileService->setApplicantType($selectedType);

    $selectedProfileData = [
      'type' => $selectedType,
    ];

    switch ($selectedType) {
      case 'unregistered_community':

        $storage = $form_state->getStorage();
        $userCommunities = $storage['userCommunities'];

        $selectedCommunity = $form_state->getValue('unregistered_community_selection');

        if ($selectedCommunity == 'new') {
          $selectedProfileData['identifier'] = $this->grantsProfileService->getUuid();
          $selectedProfileData['name'] = $this->t('New Unregistered Community')
            ->render();
          $selectedProfileData['complete'] = FALSE;

          // Redirect user to grants profile page.
          $redirectUrl = Url::fromRoute('grants_profile.edit');

          $this->grantsProfileService->setSelectedRoleData($selectedProfileData);

        }
        else {

          $selectedCommunityObject = array_filter(
            $userCommunities,
            function ($item) use ($selectedCommunity) {
              $meta = $item->getMetadata();
              if ($meta['profile_id'] == $selectedCommunity) {
                return TRUE;
              }
              return FALSE;
            }
          );

          $selectedCommunityObject = reset($selectedCommunityObject);
          $selectedMetadata = $selectedCommunityObject->getMetadata();
          $selectedContent = $selectedCommunityObject->getContent();

          $selectedProfileData['identifier'] = $selectedMetadata['profile_id'];
          $selectedProfileData['name'] = $selectedContent["companyName"];
          $selectedProfileData['complete'] = TRUE;

          $this->grantsProfileService->setSelectedRoleData($selectedProfileData);

          // Redirect user to grants profile page.
          $redirectUrl = Url::fromRoute('grants_profile.show');
        }

        $redirect = new TrustedRedirectResponse($redirectUrl->toString());
        $form_state->setResponse($redirect);

        break;

      case 'private_person':
        $userData = $this->helsinkiProfiiliUserData->getUserData();

        $selectedProfileData['identifier'] = $userData["sub"];
        $selectedProfileData['name'] = $userData["name"];
        $selectedProfileData['complete'] = TRUE;

        $this->grantsProfileService->setSelectedRoleData($selectedProfileData);

        // Redirect user to grants profile page.
        $redirectUrl = Url::fromRoute('grants_profile.show');
        $redirect = new TrustedRedirectResponse($redirectUrl->toString());
        $form_state->setResponse($redirect);

        break;

      default:
        $mandateMode = 'ypa';
        $redirectUrl = Url::fromUri($this->grantsMandateService->getUserMandateRedirectUrl($mandateMode));
        $redirect = new TrustedRedirectResponse($redirectUrl->toString());
        $form_state->setResponse($redirect);

        break;
    }
  }

}
