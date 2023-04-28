<?php

namespace Drupal\grants_oma_asiointi\Plugin\Block;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\grants_profile\GrantsProfileService;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "grants_oma_asiointi_asiointirooli_block",
 *   admin_label = @Translation("Grants Oma Asiointi Asiointirooli"),
 *   category = @Translation("Oma Asiointi")
 * )
 */
class AsiointirooliBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    GrantsProfileService $grants_profile_service
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
   */
  public function build() {

    $companyName = NULL;

    $selectedCompany = $this->grantsProfileService->getSelectedRoleData();
    if ($selectedCompany) {
      $companyName = $selectedCompany['name'];
    }

    $switchRole = Link::createFromRoute($this->t('Switch role', [], [
      'context' => 'Asiointirooli block',
    ]), 'grants_mandate.mandateform', [],
    [
      'attributes' => [
        'class' => ['link--switch-role'],
      ],
    ]);
    $logOut = Link::fromTextAndUrl(t('Log out'), Url::fromUri('base:user/logout',
    [
      'attributes' => [
        'class' => ['link--stop-mandate'],
      ],
    ]));

    $build = [
      '#theme' => 'grants_oma_asiointi_asiointirooli_block',
      '#companyName' => $companyName,
      '#switchRole' => $switchRole,
      '#logOut' => $logOut,
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
