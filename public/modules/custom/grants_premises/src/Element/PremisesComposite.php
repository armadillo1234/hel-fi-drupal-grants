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
      '#title' => t('Premise name'),
      '#access' => TRUE,
    ];

    $elements['premiseAddress'] = [
      '#type' => 'textfield',
      '#title' => t('Premise Address'),
    ];
    $elements['location'] = [
      '#type' => 'textfield',
      '#title' => t('Location'),
    ];
    $elements['streetAddress'] = [
      '#type' => 'textfield',
      '#title' => t('Street Address'),
    ];

    $elements['address'] = [
      '#type' => 'textfield',
      '#title' => t('Address'),
    ];

    $elements['postCode'] = [
      '#type' => 'textfield',
      '#title' => t('Postal Code'),
      '#size' => 10,
    ];

    $elements['studentCount'] = [
      '#type' => 'textfield',
      '#title' => t('Student Count'),
    ];

    $elements['specialStudents'] = [
      '#type' => 'textfield',
      '#title' => t('Special Students'),
    ];

    $elements['groupCount'] = [
      '#type' => 'textfield',
      '#title' => t('Group Count'),
    ];

    $elements['specialGroups'] = [
      '#type' => 'textfield',
      '#title' => t('Special Groups'),
    ];

    $elements['personnelCount'] = [
      '#type' => 'textfield',
      '#title' => t('Personnel Count'),
    ];

    $elements['totalRent'] = [
      '#type' => 'textfield',
      '#title' => t('Total Rent'),
    ];

    $elements['rentTimeBegin'] = [
      '#type' => 'datetime',
      '#title' => t('Rent time begin'),
      '#wrapper_attributes' => [
        'class' => ['hds-text-input'],
      ],
    ];
    $elements['rentTimeEnd'] = [
      '#type' => 'datetime',
      '#title' => t('Rent time end'),
      '#wrapper_attributes' => [
        'class' => ['hds-text-input'],
      ],
    ];
    $elements['free'] = [
      '#type' => 'checkbox',
      '#title' => t('Is premise free'),
    ];
    $elements['isOthersUse'] = [
      '#type' => 'checkbox',
      '#title' => t('Is other use'),
    ];
    $elements['isOwnedByApplicant'] = [
      '#type' => 'radios',
      '#options' => [
        1 => t('Yes', [], $tOpts),
        0 => t('No', [], $tOpts),
      ],
      '#title' => t('Applicant owns property'),
    ];
    $elements['isOwnedByCity'] = [
      '#type' => 'radios',
      '#options' => [
        1 => t('Yes', [], $tOpts),
        0 => t('No', [], $tOpts),
      ],
      '#title' => t('City owns the property'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function processWebformComposite(&$element, FormStateInterface $form_state, &$complete_form) {
    $element = parent::processWebformComposite($element, $form_state, $complete_form);

    $elementValue = $element['#value'];

    if ($elementValue["isOwnedByCity"] === "false") {
      $element["isOwnedByCity"]["#default_value"] = 0;
    }
    if ($elementValue["isOwnedByCity"] === "true") {
      $element["isOwnedByCity"]["#default_value"] = 1;
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

}
