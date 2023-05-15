<?php

namespace Drupal\grants_handler\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\helfi_helsinki_profiili\HelsinkiProfiiliUserData;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a service page block.
 *
 * @Block(
 *   id = "grants_handler_service_page_anon_block",
 *   admin_label = @Translation("Service Page Anon Block"),
 *   category = @Translation("Custom")
 * )
 */
class ServicePageAnonBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The helfi_helsinki_profiili service.
   *
   * @var \Drupal\helfi_helsinki_profiili\HelsinkiProfiiliUserData
   */
  protected $helfiHelsinkiProfiili;

  /**
   * Constructs a new ServicePageBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\helfi_helsinki_profiili\HelsinkiProfiiliUserData $helfi_helsinki_profiili
   *   The helfi_helsinki_profiili service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, HelsinkiProfiiliUserData $helfi_helsinki_profiili) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->helfiHelsinkiProfiili = $helfi_helsinki_profiili;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('helfi_helsinki_profiili.userdata')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {

    $getApplicantType = $this->build();

    $correctApplicantType = $getApplicantType['content']['#applicantType'];

    return AccessResult::allowedIf(\Drupal::currentUser()->isAuthenticated() && !$correctApplicantType);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $node = \Drupal::routeMatch()->getParameter('node');

    $webformId = $node->get('field_webform')->target_id;

    $applicantTypes = $node->get('field_hakijatyyppi')->getValue();

    $profileService = \Drupal::service('grants_profile.service');
    $currentRole = $profileService->getSelectedRoleData();
    $currentRoleType = NULL;
    if ($currentRole) {
      $currentRoleType = $currentRole['type'];
    }

    $isCorrectApplicantType = FALSE;

    foreach ($applicantTypes as $applicantType) {
      if (in_array($currentRoleType, $applicantType)) {
        $isCorrectApplicantType = TRUE;
      }
    }

    $link = Link::createFromRoute($this->t('Change your role'), 'grants_mandate.mandateform',
    [],
    [
      'attributes' => [
        'class' => ['hds-button', 'hds-button--primary'],
      ],
    ]);

    $markup = '<p>' . $this->t('You do not have the necessary authorizations to make an application.') . '</p>' . $link->toString();

    $build['content'] = [
      '#markup' => $markup,
      '#applicantType' => $isCorrectApplicantType,
    ];

    return $build;
  }

}
