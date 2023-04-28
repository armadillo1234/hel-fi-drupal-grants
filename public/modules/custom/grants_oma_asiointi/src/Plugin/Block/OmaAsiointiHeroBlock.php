<?php

namespace Drupal\grants_oma_asiointi\Plugin\Block;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\grants_profile\GrantsProfileService;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "grants_oma_asiointi_hero_block",
 *   admin_label = @Translation("Grants Oma Asiointi Hero"),
 *   category = @Translation("Oma Asiointi")
 * )
 */
class OmaAsiointiHeroBlock extends BlockBase implements ContainerFactoryPluginInterface {

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

    $selectedRole = $this->grantsProfileService->getSelectedRoleData();
    $title = $selectedRole['name'];
    $roleType = $selectedRole['type'];

    $build = [
      '#theme' => 'grants_oma_asiointi_hero_block',
      '#title' => $title,
      '#roleType' => $roleType,
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
