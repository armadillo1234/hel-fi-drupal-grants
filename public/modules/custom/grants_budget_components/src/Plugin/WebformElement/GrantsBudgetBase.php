<?php

namespace Drupal\grants_budget_components\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformElement\WebformCompositeBase;
use Drupal\Core\Render\Element as RenderElement;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Base component for budget webform elements.
 */
class GrantsBudgetBase extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    // Here you define your webform element's default properties,
    // which can be inherited.
    //
    // @see \Drupal\webform\Plugin\WebformElementBase::defaultProperties
    // @see \Drupal\webform\Plugin\WebformElementBase::defaultBaseProperties
    return [
      'multiple' => '',
      'size' => '',
      'minlength' => '',
      'maxlength' => '',
      'placeholder' => '',
      'incomeGroup' => '',
    ] + parent::defineDefaultProperties();
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    // Here you can define and alter a webform element's properties UI.
    // Form element property visibility and default values are defined via
    // ::defaultProperties.
    //
    // @see \Drupal\webform\Plugin\WebformElementBase::form
    // @see \Drupal\webform\Plugin\WebformElement\TextBase::form
    $form['element']['incomeGroup'] = [
      '#type' => 'select',
      '#title' => $this->t('Income group'),
      '#options' => $this->getIncomeGroupOptions(),
    ];

    return $form;
  }

  /**
   * Get income group names.
   *
   * @return array
   *   Income group names.
   */
  protected function getIncomeGroupOptions() {
    $tOpts = ['context' => 'grants_budget_components'];

    return [
      'general' => t('General Budget', [], $tOpts),
      'budgetForProjectAndDevelopment' => t('Budget for project and development', [], $tOpts),
      'budgetForOperatingAndArtsTeaching' => t('Budget for operating and arts teaching'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function formatHtmlItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []): array|string {
    $format = $this->getItemFormat($element);
    $items = [];
    $composite_elements = $this->getInitializedCompositeElement($element);
    foreach (RenderElement::children($composite_elements) as $composite_key) {

      if (in_array($composite_key, ['incomeGroupName', 'costGroupName'])) {
        continue;
      }

      $composite_element = $composite_elements[$composite_key];
      $composite_title = (isset($composite_element['#title']) && $format !== 'raw') ? $composite_element['#title'] : $composite_key;
      $composite_value = $this->formatCompositeHtml($element, $webform_submission, ['composite_key' => $composite_key] + $options);
      if ($composite_value !== '') {
        $items[$composite_key] = [
          '#type' => 'inline_template',
          '#template' => '<b>{{ title }}:</b> {{ value }}',
          '#context' => [
            'title' => $composite_title,
            'value' => $composite_value,
          ],
        ];
      }
    }
    return $items;
  }

}
