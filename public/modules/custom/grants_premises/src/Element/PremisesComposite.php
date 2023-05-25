<?php

namespace Drupal\grants_premises\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Element\WebformCompositeBase;

/**
 * Provides a 'premises_composite'.
 *
 * Webform composites contain a group of sub-elements.
 *
 *
 * IMPORTANT:
 * Webform composite can not contain multiple value elements (i.e. checkboxes)
 * or composites (i.e. premises_composite)
 *
 * @FormElement("premises_composite")
 *
 * @see \Drupal\webform\Element\WebformCompositeBase
 */
class PremisesComposite extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo(): array {
    return parent::getInfo() + ['#theme' => 'premises_composite'];
  }

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements(array $element): array {
    $elements = [];
    $tOpts = ['context' => 'grants_premises'];

    $elements['premiseName'] = [
      '#type' => 'textfield',
      '#title' => t('Premise name', [], $tOpts),
      '#access' => TRUE,
    ];

    $elements['premiseType'] = [
      '#type' => 'select',
      '#title' => t('Premise type', [], $tOpts),
      '#access' => TRUE,
      '#options' => self::getTilaTypes(),
    ];

    $elements['premiseAddress'] = [
      '#type' => 'textfield',
      '#title' => t('Premise Address', [], $tOpts),
    ];
    $elements['location'] = [
      '#type' => 'textfield',
      '#title' => t('Location', [], $tOpts),
    ];
    $elements['streetAddress'] = [
      '#type' => 'textfield',
      '#title' => t('Street Address', [], $tOpts),
    ];

    $elements['address'] = [
      '#type' => 'textfield',
      '#title' => t('Address', [], $tOpts),
    ];

    $elements['postCode'] = [
      '#type' => 'textfield',
      '#title' => t('Post Code', [], $tOpts),
      '#size' => 10,
    ];

    $elements['studentCount'] = [
      '#type' => 'textfield',
      '#title' => t('Student Count', [], $tOpts),
    ];

    $elements['specialStudents'] = [
      '#type' => 'textfield',
      '#title' => t('Special Students', [], $tOpts),
    ];

    $elements['groupCount'] = [
      '#type' => 'textfield',
      '#title' => t('Group Count', [], $tOpts),
    ];

    $elements['specialGroups'] = [
      '#type' => 'textfield',
      '#title' => t('Special Groups', [], $tOpts),
    ];

    $elements['personnelCount'] = [
      '#type' => 'textfield',
      '#title' => t('Personnel Count', [], $tOpts),
    ];

    $elements['totalRent'] = [
      '#type' => 'textfield',
      '#title' => t('Total Rent', [], $tOpts),
    ];

    $elements['rentTimeBegin'] = [
      '#type' => 'datetime',
      '#title' => t('Rent time begin', [], $tOpts),
      '#wrapper_attributes' => [
        'class' => ['hds-text-input'],
      ],
    ];
    $elements['rentTimeEnd'] = [
      '#type' => 'datetime',
      '#title' => t('Rent time end', [], $tOpts),
      '#wrapper_attributes' => [
        'class' => ['hds-text-input'],
      ],
    ];
    $elements['free'] = [
      '#type' => 'radios',
      '#options' => [
        1 => t('Yes', [], $tOpts),
        0 => t('No', [], $tOpts),
      ],
      '#title' => t('Is premise free', [], $tOpts),
    ];
    $elements['isOthersUse'] = [
      '#type' => 'radios',
      '#options' => [
        1 => t('Yes', [], $tOpts),
        0 => t('No', [], $tOpts),
      ],
      '#title' => t('Is other use', [], $tOpts),
    ];
    $elements['isOwnedByApplicant'] = [
      '#type' => 'radios',
      '#options' => [
        1 => t('Yes', [], $tOpts),
        0 => t('No', [], $tOpts),
      ],
      '#title' => t('Applicant owns property', [], $tOpts),
    ];
    $elements['isOwnedByCity'] = [
      '#type' => 'radios',
      '#options' => [
        1 => t('Yes', [], $tOpts),
        0 => t('No', [], $tOpts),
      ],
      '#title' => t('City owns the property', [], $tOpts),
    ];

    /* Remove all elements from elements that are not explicitly selected
    for this form. Hopefully this fixes issues with data fields. */
    foreach ($element as $fieldName => $value) {
      if (str_contains($fieldName, '__access')) {
        $fName = str_replace('__access', '', $fieldName);
        $fName = str_replace('#', '', $fName);
        if ($value === FALSE && isset($elements[$fName])) {
          unset($elements[$fName]);
        }
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function processWebformComposite(&$element, FormStateInterface $form_state, &$complete_form) {
    $element = parent::processWebformComposite($element, $form_state, $complete_form);

    $elementValue = $element['#value'];

    if (isset($element["isOwnedByCity"]) && $elementValue["isOwnedByCity"] === "false") {
      $element["isOwnedByCity"]["#default_value"] = 0;
    }
    if (isset($element["isOwnedByCity"]) && $elementValue["isOwnedByCity"] === "true") {
      $element["isOwnedByCity"]["#default_value"] = 1;
    }

    if (isset($element["isOthersUse"]) && $elementValue["isOthersUse"] === "false") {
      $element["isOthersUse"]["#default_value"] = 0;
    }
    if (isset($element["isOthersUse"]) && $elementValue["isOthersUse"] === "true") {
      $element["isOthersUse"]["#default_value"] = 1;
    }

    if (isset($element["isOwnedByApplicant"]) && $elementValue["isOwnedByApplicant"] === "false") {
      $element["isOwnedByApplicant"]["#default_value"] = 0;
    }
    if (isset($element["isOwnedByApplicant"]) && $elementValue["isOwnedByApplicant"] === "true") {
      $element["isOwnedByApplicant"]["#default_value"] = 1;
    }

    return $element;
  }

  /**
   * Build select option from profile data.
   *
   * The default selection CANNOT be done here.
   *
   * @param array $element
   *   Element to change.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   *
   * @return array
   *   Updated element
   *
   * @see grants_handler.module
   */
  public static function buildPremiseListOptions(array $element, FormStateInterface $form_state): array {

    return $element;

  }

  /**
   * Get tila types.
   *
   * @return array
   *   Translated tila types.
   */
  public static function getTilaTypes() {
    $tOpts = ['context' => 'grants_premises'];
    return [
      'Näyttelytila' => t('Exhibition space', [], $tOpts),
      'Esitystila' => t('Performance space', [], $tOpts),
      'Erillinen harjoittelutila tai muu taiteellisen työskentelyn tila' =>
      t('A separate practice space or other space for artistic work', [], $tOpts),

    ];
  }

}
