<?php

namespace Drupal\grants_premises\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformElement\WebformCompositeBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Provides a 'premises_composite' element.
 *
 * @WebformElement(
 *   id = "premises_composite",
 *   label = @Translation("Grants premises"),
 *   description = @Translation("Provides a premises elemebnt."),
 *   category = @Translation("Hel.fi elements"),
 *   multiline = TRUE,
 *   composite = TRUE,
 *   states_wrapper = TRUE,
 * )
 *
 * @see \Drupal\webform\Plugin\WebformElement\WebformCompositeBase
 * @see \Drupal\webform\Plugin\WebformElementBase
 * @see \Drupal\webform\Plugin\WebformElementInterface
 * @see \Drupal\webform\Annotation\WebformElement
 */
class PremisesComposite extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    // Here you define your webform element's default properties,
    // which can be inherited.
    //
    // @see \Drupal\webform\Plugin\WebformElementBase::defaultProperties
    // @see \Drupal\webform\Plugin\WebformElementBase::defaultBaseProperties
    return [] + parent::defineDefaultProperties();
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
    $form['element']['premiseFields'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#multiple' => TRUE,
      '#title' => $this->t('Premise fields collected'),
      '#options' => self::buildPremiseFieldsOptions(),
    ];

    return $form;
  }

  /**
   * Build fieldlist for UI.
   *
   * @return array
   *   Updated element
   *
   * @see grants_handler.module
   */
  public static function buildPremiseFieldsOptions(): array {

    return [
      'premiseType' => t('Premise Type'),
      'premiseName' => t('Premise Name'),
      'premiseAddress' => t('Premise Address'),
      'location' => t('Premise location'),
      'streetAddress' => t('Street Address'),
      'address' => t('Address'),
      'postCode' => t('Postal Code'),
      'studentCount' => t('Student Count'),
      'specialStudents' => t('Special Students'),
      'groupCount' => t('Group Count'),
      'specialGroups' => t('Special Groups'),
      'personnelCount' => t('Personnel Count'),
      'totalRent' => t('Total Rent'),
      'rentTimeBegin' => t('Rent time begin'),
      'rentTimeEnd' => t('Rent time end'),
      'free' => t('Free'),
    ];

  }

  /**
   * {@inheritdoc}
   */
  protected function formatHtmlItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []): array|string {
    return $this->formatTextItemValue($element, $webform_submission, $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function formatTextItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []): array {
    $value = $this->getValue($element, $webform_submission, $options);
    $lines = [];
    foreach ($value as $fieldName => $fieldValue) {
      $webformElement = $element["#webform_composite_elements"][$fieldName];
      if ($webformElement['#access'] !== FALSE) {
        $lines[] = '<strong>' . $webformElement['#title']->render() . '</strong>';
        $lines[] = $fieldValue . '<br>';
      }
    }

    return $lines;
  }

}
