<?php

namespace Drupal\grants_handler\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\helfi_helsinki_profiili\HelsinkiProfiiliUserData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Carbon\Carbon;
use Drupal\Core\Url;

/**
 * Provides a service page block.
 *
 * @Block(
 *   id = "grants_handler_service_page_auth_block",
 *   admin_label = @Translation("Service Page Auth Block"),
 *   category = @Translation("Custom")
 * )
 */
class ServicePageAuthBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\helfi_helsinki_profiili\HelsinkiProfiiliUserData $helfiHelsinkiProfiili
   *   The helfi_helsinki_profiili service.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, HelsinkiProfiiliUserData $helfiHelsinkiProfiili) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->helfiHelsinkiProfiili = $helfiHelsinkiProfiili;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
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

    $access = $this->checkFormAccess();

    return AccessResult::allowedIf($access);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $node = \Drupal::routeMatch()->getParameter('node');

    $webformId = $node->get('field_webform')->target_id;

    // No webform reference, no need for this block.
    if (!$webformId) {
      return;
    }
    // Create link for new application.
    $link = Link::createFromRoute($this->t('New application'), 'grants_handler.new_application',
      [
        'webform_id' => $webformId,
      ],
      [
        'attributes' => [
          'class' => ['hds-button', 'hds-button--primary'],
        ],
      ]);

    $build['content'] = [
      '#markup' => $link->toString(),
    ];
    $build['#cache']['contexts'] = [
      'languages:language_content',
      'url.path',
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags(): array {
    $node = \Drupal::routeMatch()->getParameter('node');
    return Cache::mergeTags(parent::getCacheTags(), $node->getCacheTags());
  }

  /**
   * Builds the link content as LinkItem values.
   *
   * @return array|bool
   *   False if nothing to show, otherwise ready to use array for LinkItem.
   */
  public function buildAsTprLink() {

    $currentUser = \Drupal::currentUser();

    if ($currentUser->isAnonymous()) {
      return FALSE;
    }

    $roles = $currentUser->getRoles();
    if (!in_array('helsinkiprofiili', $roles)) {
      return FALSE;
    }

    $node = \Drupal::routeMatch()->getParameter('node');

    $webformId = $node->get('field_webform')->target_id;

    $access = $this->checkFormAccess();

    if (!$access) {
      return FALSE;
    }

    // Create link for new application.
    $link = Url::fromRoute('grants_handler.new_application',
      [
        'webform_id' => $webformId,
      ], ['absolute' => TRUE]);

    $linkArr = [
      'title' => $this->t('New application'),
      'uri' => $link->toString(),
      'options' => [],
      '_attributes' => [],
    ];

    return $linkArr;
  }

  /**
   * Checks if form is open and if user role has permission to it.
   *
   * @return bool
   *   Boolean value telling if user can see the new application button.
   */
  private function checkFormAccess() {

    $access = FALSE;

    $node = \Drupal::routeMatch()->getParameter('node');

    $profileService = \Drupal::service('grants_profile.service');
    $selectedCompany = $profileService->getSelectedRoleData();

    $applicationOpen = $node->get('field_application_open')->value;
    $applicationContinuous = (bool) $node->get('field_application_continuous')->value;
    $applicationPeriodStart = new Carbon($node->get('field_application_period')->value);
    $applicationPeriodEnd = new Carbon($node->get('field_application_period')->end_value);
    $now = new Carbon();

    $applicationOpenByTime = $now->between($applicationPeriodStart, $applicationPeriodEnd);

    $webformId = $node->get('field_webform')->target_id;

    if (!$webformId) {
      $access = FALSE;
    }

    if (($applicationOpenByTime || $applicationContinuous) && $applicationOpen == '1') {
      $access = TRUE;
    }

    $webform = \Drupal::entityTypeManager()->getStorage('webform')->load($webformId);
    $thirdPartySettings = $webform->getThirdPartySettings('grants_metadata');

    // Old applications have only single selection, we need to support this.
    if (!is_array($thirdPartySettings["applicantTypes"])) {
      $formApplicationTypes[] = $thirdPartySettings["applicantTypes"];
    }
    else {
      $formApplicationTypes = array_values($thirdPartySettings["applicantTypes"]);
    }

    if (!in_array($selectedCompany["type"], $formApplicationTypes)) {
      $access = FALSE;
    }

    return $access;
  }

}
