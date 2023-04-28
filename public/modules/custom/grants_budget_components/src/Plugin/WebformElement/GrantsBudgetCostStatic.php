<?php

namespace Drupal\grants_budget_components\Plugin\WebformElement;

/**
 * Provides a 'grants_budget_cost_static' element.
 *
 * @WebformElement(
 *   id = "grants_budget_cost_static",
 *   label = @Translation("GrantsBudgetCostStatic"),
 *   description = @Translation("Provides a GrantsBudgetCostStatic."),
 *   category = @Translation("GrantsBudgetCostStatic"),
 *   multiline = TRUE,
 *   composite = TRUE,
 *   states_wrapper = TRUE,
 * )
 *
 * @see \Drupal\webform_example_composite\Element\WebformExampleComposite
 * @see \Drupal\webform\Plugin\WebformElement\WebformCompositeBase
 * @see \Drupal\webform\Plugin\WebformElementBase
 * @see \Drupal\webform\Plugin\WebformElementInterface
 * @see \Drupal\webform\Annotation\WebformElement
 */
class GrantsBudgetCostStatic extends GrantsBudgetBase {

  /**
   * {@inheritdoc}
   */
  protected function getIncomeGroupOptions() {
    $values = parent::getIncomeGroupOptions();
    $tOpts = ['contect' => 'grants_budget_components'];
    $additionalValues = [
      "subventionUseCosts" => t('Subvention use costs', [], $tOpts),
      "costsForServicesAcquired" => t('Costs for services acquired', [], $tOpts),
      "costsForMaterialsSuppliesAndGoods" => t('Costs for material supplies and goods', [], $tOpts),
      "otherCosts" => t('Other costs', [], $tOpts),
      "useOfCustomerFeeIncome" => t('Use of customer fee income', [], $tOpts),
    ];

    return array_merge($values, $additionalValues);
  }

}
