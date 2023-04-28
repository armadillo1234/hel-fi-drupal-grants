<?php

namespace Drupal\grants_budget_components\Plugin\WebformElement;

/**
 * Provides a 'grants_budget_other_cost' element.
 *
 * @WebformElement(
 *   id = "grants_budget_other_cost",
 *   label = @Translation("GrantsBudgetOtherCost"),
 *   description = @Translation("Provides a GrantsBudgetOtherCost."),
 *   category = @Translation("GrantsBudgetOtherCost"),
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
class GrantsBudgetOtherCost extends GrantsBudgetBase {
}
