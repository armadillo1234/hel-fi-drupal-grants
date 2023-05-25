<?php

namespace Drupal\grants_webform_summation_field\Element;

use Drupal\Core\Render\Element\FormElement;

/**
 * Provides a webform element for an grants_webform_summation_field.
 *
 * @FormElement("grants_webform_summation_field")
 */
class GrantsWebformSummationField extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);

    return [
      '#input' => FALSE,
      '#size' => 60,
      '#default_value' => 0,
      '#pre_render' => [
        [$class, 'preRenderGrantsWebformSummationFieldElement'],
      ],
      '#theme' => 'grants_webform_summation_field',
    ];
  }

  /**
   * Description.
   *
   * @param array $element
   *   Element.
   *
   * @return mixed
   *   Return value.
   */
  public static function preRenderGrantsWebformSummationFieldElement(array $element): mixed {
    $field = '';
    $column = '';
    $fieldarray = [];
    foreach ($element['#collect_field'] as $key => $value) {
      if ($value !== 0) {
        if (strstr($element['#collect_field'][$key], '%%')) {
          [$field, $column] = explode('%%', $element['#collect_field'][$key]);
        }
        else {
          $fieldarray[] = $element['#collect_field'][$key];
        }
      }
    }

    $element['#theme_wrappers'][] = 'form_element';
    $element['#wrapper_attributes']['id'] = $element['#id'] . '--wrapper';
    $element['#attributes']['id'] = $element['#id'];
    $element['#attributes']['name'] = $element['#name'];
    $element['#attributes']['value'] = $element['#value'];
    $summationType = 'integer';
    $displayType = 'integer';
    $formItem = 'text_field';
    if (isset($element['#form_item'])) {
      $formItem = $element['#form_item'];
    }
    if ($formItem === 'hidden') {
      $element['#title_display'] = 'none';
      $element['#description_display'] = 'none';
      $element['#attributes']['readonly'] = 'readonly';
      $element['#attributes']['style'] = 'display:none;';
    }
    $element['#type'] = 'text_field';
    if (isset($element['#data_type'])) {
      $summationType = $element['#data_type'];
    }
    if (isset($element['#display_type'])) {
      $displayType = $element['#display_type'];
    }
    if (count($fieldarray) > 0) {
      $element['#attached']['drupalSettings']['sumFields'][$element['#id']] = [
        'sumFieldId' => $element['#id'],
        'fields' => $fieldarray,
        'summationType' => $summationType,
        'displayType' => $displayType,
      ];
    }
    else {
      $element['#attached']['drupalSettings']['sumFields'][$element['#id']] = [
        'sumFieldId' => $element['#id'],
        'fieldName' => $field,
        'columnName' => $column,
        'summationType' => $element['#data_type'],
      ];
    }

    // Add class name to wrapper attributes.
    $class_name = str_replace('_', '-', $element['#type']);
    static::setAttributes($element, ['js-' . $class_name, $class_name]);

    return $element;
  }

}
