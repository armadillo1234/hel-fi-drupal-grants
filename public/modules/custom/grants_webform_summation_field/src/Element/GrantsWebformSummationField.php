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
      '#input' => TRUE,
      '#size' => 60,
      '#value' => 0,
      '#pre_render' => [
        [$class, 'preRenderGrantsWebformSummationFieldElement'],
      ],
      '#theme' => 'grants_webform_summation_field',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function preRenderGrantsWebformSummationFieldElement($element) {
    $field = '';
    $column = '';
    foreach ($element['#collect_field'] as $key => $value) {
      if ($value !== 0) {
        list($field, $column) = explode('%%', $element['#collect_field'][$key]);
      }
    }

    $element['#theme_wrappers'][] = 'form_element';
    $element['#wrapper_attributes']['id'] = $element['#id'] . '--wrapper';
    $element['#attributes']['id'] = $element['#id'];
    $element['#attributes']['name'] = $element['#name'];
    $element['#attributes']['value'] = $element['#value'];
    $element['#attached']['drupalSettings']['sumFieldName'] = $element['#id'];
    $element['#attached']['drupalSettings']['fieldName'] = $field;
    $element['#attached']['drupalSettings']['columnName'] = $column;

    // Add class name to wrapper attributes.
    $class_name = str_replace('_', '-', $element['#type']);
    static::setAttributes($element, ['js-' . $class_name, $class_name]);

    return $element;
  }

}
