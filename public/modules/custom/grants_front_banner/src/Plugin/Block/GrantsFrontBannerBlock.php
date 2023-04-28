<?php

namespace Drupal\grants_front_banner\Plugin\Block;

use Drupal\user\Form\UserLoginForm;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\grants_profile\GrantsProfileService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides a grants front banner block.
 *
 * @Block(
 *   id = "grants_front_banner",
 *   admin_label = @Translation("Grants Front Banner"),
 *   category = @Translation("Oma Asiointi")
 * )
 */
class GrantsFrontBannerBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The grants_profile.service service.
   *
   * @var \Drupal\grants_profile\GrantsProfileService
   */
  protected GrantsProfileService $grantsProfileService;

  /**
   * Construct block object.
   *
   * @param array $configuration
   *   Block config.
   * @param string $plugin_id
   *   Plugin.
   * @param mixed $plugin_definition
   *   Plugin def.
   * @param \Drupal\grants_profile\GrantsProfileService $grants_profile_service
   *   The grants_profile.service service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    GrantsProfileService $grants_profile_service,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->grantsProfileService = $grants_profile_service;
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
      $container->get('grants_profile.service'),
    );
  }

  /**
   * {@inheritdoc}
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function build() {

    // @todo Refactor to support other profile types https://helsinkisolutionoffice.atlassian.net/browse/AU-662
    $selectedCompany = $this->grantsProfileService->getSelectedRoleData();

    $getGrantsProfile = NULL;
    if ($selectedCompany) {
      $getGrantsProfile = $this->grantsProfileService->getGrantsProfile($selectedCompany);
    }

    $logged_in = \Drupal::currentUser()->isAuthenticated();
    $fillinfo = Url::fromRoute('grants_profile.edit');
    $loginForm = \Drupal::formBuilder()->getForm(UserLoginForm::class);

    $build = [
      '#theme' => 'grants_front_banner',
      '#loggedin' => $logged_in,
      '#fillinfo' => $fillinfo,
      '#loginform' => $loginForm,
      '#getgrantsprofile' => $getGrantsProfile,
    ];
    return $build;
  }

}
