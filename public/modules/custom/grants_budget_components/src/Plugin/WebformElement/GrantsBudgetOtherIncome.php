<?php

namespace Drupal\grants_budget_components\Plugin\WebformElement;

/**
 * Provides a 'grants_budget_other_income' element.
 *
 * @WebformElement(
 *   id = "grants_budget_other_income",
 *   label = @Translation("GrantsBudgetOtherIncome"),
 *   description = @Translation("Provides a GrantsBudgetOtherIncome."),
 *   category = @Translation("GrantsBudgetOtherIncome"),
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
class GrantsBudgetOtherIncome extends GrantsBudgetBase {
}
