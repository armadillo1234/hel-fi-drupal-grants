<?php

namespace Drupal\grants_budget_components\Plugin\WebformElement;

/**
 * Provides a 'grants_budget_income_static' element.
 *
 * @WebformElement(
 *   id = "grants_budget_income_static",
 *   label = @Translation("GrantsBudgetIncomeStatic"),
 *   description = @Translation("Provides a GrantsBudgetIncomeStatic."),
 *   category = @Translation("GrantsBudgetIncomeStatic"),
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
class GrantsBudgetIncomeStatic extends GrantsBudgetBase {
}
