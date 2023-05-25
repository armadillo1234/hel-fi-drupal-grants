<?php

namespace Drupal\grants_profile\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\grants_profile\Form\GrantsProfileFormRegisteredCommunity;
use Drupal\grants_profile\GrantsProfileService;
use Drupal\helfi_helsinki_profiili\HelsinkiProfiiliUserData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Returns responses for Grants Profile routes.
 */
class GrantsProfileController extends ControllerBase {


  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * Grants profile service.
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
   * ModalFormContactController constructor.
   *
   * @param \Drupal\Core\Form\FormBuilder $form_builder
   *   The form builder.
   * @param \Drupal\grants_profile\GrantsProfileService $grantsProfileService
   *   Grants profile service.
   * @param \Drupal\helfi_helsinki_profiili\HelsinkiProfiiliUserData $helsinkiProfiiliUserData
   *   Data for Helsinki Profile.
   */
  public function __construct(
    FormBuilder $form_builder,
    GrantsProfileService $grantsProfileService,
    HelsinkiProfiiliUserData $helsinkiProfiiliUserData
  ) {
    $this->formBuilder = $form_builder;
    $this->grantsProfileService = $grantsProfileService;
    $this->helsinkiProfiiliUserData = $helsinkiProfiiliUserData;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('form_builder'),
      $container->get('grants_profile.service'),
      $container->get('helfi_helsinki_profiili.userdata')
    );
  }

  /**
   * Builds the response.
   *
   * @return array|\Laminas\Diactoros\Response\RedirectResponse
   *   Data to render
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Drupal\helfi_helsinki_profiili\TokenExpiredException
   */
  public function viewProfile(): array|RedirectResponse {
    $selectedRoleData = $this->grantsProfileService->getSelectedRoleData();

    if ($selectedRoleData == NULL) {
      $this->messenger()
        ->addError($this->t('No profile data available, select company'), TRUE);

      return new RedirectResponse('/asiointirooli-valtuutus');
    }
    else {

      $profile = $this->grantsProfileService->getGrantsProfileContent($selectedRoleData, TRUE);

      if (empty($profile)) {
        $editProfileUrl = Url::fromRoute(
          'grants_profile.edit'
        );
        return new RedirectResponse($editProfileUrl->toString());
      }
    }

    $build['#theme'] = 'own_profile_' . $selectedRoleData["type"];
    $build['#profile'] = $profile;
    $build['#userData'] = $this->helsinkiProfiiliUserData->getUserProfileData();

    $editProfileUrl = Url::fromRoute(
      'grants_profile.edit',
      [],
      [
        'attributes' => [
          'data-drupal-selector' => 'profile-edit-link',
          'class' => ['hds-button', 'hds-button--primary'],
        ],
      ]
    );
    $editProfileText = [
      '#theme' => 'edit-label-with-icon',
      '#icon' => 'pen-line',
      '#text_label' => $this->t('Edit own information'),
    ];

    $build['#editProfileLink'] = Link::fromTextAndUrl($editProfileText, $editProfileUrl);

    $build['#roles'] = GrantsProfileFormRegisteredCommunity::getOfficialRoles();

    return $build;
  }

  /**
   * Edit profile form.
   *
   * @return array
   *   Build data
   */
  public function editProfile(): array {

    $build = [];
    $build['#theme'] = 'edit_own_profile';

    $selectedRoleData = $this->grantsProfileService->getSelectedRoleData();

    $formObject = NULL;

    if ($selectedRoleData['type'] == 'private_person') {
      $formObject = $this->formBuilder->getForm('\Drupal\grants_profile\Form\GrantsProfileFormPrivatePerson');
    }

    if ($selectedRoleData['type'] == 'registered_community') {
      $formObject = $this->formBuilder->getForm('\Drupal\grants_profile\Form\GrantsProfileFormRegisteredCommunity');
    }

    if ($selectedRoleData['type'] == 'unregistered_community') {
      $formObject = $this->formBuilder->getForm('\Drupal\grants_profile\Form\GrantsProfileFormUnregisteredCommunity');
    }

    $build['#profileForm'] = $formObject;
    return $build;

  }

  /**
   * Redirect to my service page.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to profile page.
   */
  public function redirectToMyServices(): RedirectResponse {
    $showtProfileUrl = Url::fromRoute(
      'grants_profile.show'
    );
    return new RedirectResponse($showtProfileUrl->toString());
  }

}
