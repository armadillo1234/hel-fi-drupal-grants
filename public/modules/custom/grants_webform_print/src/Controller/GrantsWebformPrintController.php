<?php

declare(strict_types=1);

namespace Drupal\grants_webform_print\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\Webform;

/**
 * Returns responses for Webform Printify routes.
 */
class GrantsWebformPrintController extends ControllerBase {

  /**
   * Builds the response.
   *
   * @param \Drupal\webform\Entity\Webform $webform
   *   Webform to print.
   *
   * @return array
   *   Render array.
   */
  public function build(Webform $webform): array {

    /** @var \Drupal\webform\WebformTranslationManager $wftm */
    $wftm = \Drupal::service('webform.translation_manager');

    // Load all translations for this webform.
    $currentLanguage = \Drupal::languageManager()->getCurrentLanguage();
    $elementTranslations = $wftm->getElements($webform, $currentLanguage->getId());

    $webformArray = $webform->getElementsDecoded();
    // Pass decoded array & translations to traversing.
    $webformArray = $this->traverseWebform($webformArray, $elementTranslations);

    unset($webformArray['actions']);

    // Webform.
    return [
      '#theme' => 'grants_webform_print_webform',
      '#webform' => $webformArray,
    ];
  }

  /**
   * Page title callback.
   *
   * @param \Drupal\webform\Entity\Webform $webform
   *   Webform to print.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string|null
   *   Title to show.
   */
  public function title(Webform $webform) {
    return $webform->label();
  }

  /**
   * Traverse through a webform to make changes to fit the print styles.
   *
   * @param array $webformArray
   *   The Webform in question.
   * @param array $elementTranslations
   *   Translations for elements.
   *
   * @return array
   *   If there is translated value for given field, they're here.
   */
  private function traverseWebform(array $webformArray, array $elementTranslations): array {
    $transfromed = [];
    foreach ($webformArray as $key => $item) {
      $transfromed[$key] = $this->fixWebformElement($item, $key, $elementTranslations);
    }
    return $transfromed;
  }

  /**
   * Clean out unwanted things from form elements.
   *
   * @param array $element
   *   Element to fix.
   * @param string $key
   *   Key on the form.
   * @param array $translatedFields
   *   If there is translated value for given field, they're here.
   */
  private function fixWebformElement(array $element, string $key, array $translatedFields): array {

    // Remove states from printing.
    unset($element["#states"]);

    // In case of custom component, the element parts are in #element
    // so we need to sprad those out for printing.
    if (isset($element['#element'])) {
      $elements = $element['#element'];
      unset($element['#element']);
      $element = [
        ...$element,
        ...$elements,
      ];
    }
    // Look for non render array parts from element.
    $children = array_filter(array_keys($element), function ($key) {
      return !str_contains($key, '#');
    });

    // If there is some, then loop as long as there is som.
    if ($children) {
      foreach ($children as $childKey) {
        $element[$childKey] = $this->fixWebformElement($element[$childKey], $childKey, $translatedFields);
      }
    }

    // If no id for the field, we get warnigns.
    $element['#id'] = $key;

    // Force description display after element.
    $element['#description_display'] = 'after';
    if (isset($element['#attributes'])) {
      if (isset($element['#attributes']['class']) && $element['#attributes']['class'][0] == 'grants-profile--imported-section') {
        unset($element['#attributes']['class'][0]);
      }
    }
    // Field type specific alters.
    if (isset($element['#type'])) {
      // Make wizard pages show as containers.
      if (isset($element['#help'])) {
        if (isset($element['##description'])) {
          $element['#description'] = $element['#description'] . '<br>' . $element['#help'];
        }
        else {
          $element['#description'] = $element['#help'];
        }
        unset($element['#help']);
      }

      if ($element['#type'] === 'webform_wizard_page') {
        $element['#type'] = 'container';
      }
      else {
        $element['#attributes']['readonly'] = 'readonly';
      }
      // Custom components as select.
      if ($element['#type'] === 'community_address_composite') {
        $element['#type'] = 'textarea';
      }
      if ($element['#type'] === 'community_officials_composite') {
        $element['#type'] = 'textarea';
      }
      if ($element['#type'] === 'bank_account_composite') {
        $element['#type'] = 'textfield';
      }
      if ($element['#type'] === 'email') {
        $element['#type'] = 'textfield';
      }
      // Subventions as hidden textfield.
      if ($element['#type'] === 'grants_compensations') {
        $element['#type'] = 'textfield';
        $element["#attributes"]["class"][] = 'hide-input';
      }

      // @todo Refactor to use twigs https://helsinkisolutionoffice.atlassian.net/browse/AU-927
      // Premises as hidden textfield.
      if ($element['#type'] === 'premises_composite') {
        $element['#type'] = 'markup';
        $element['#markup'] = '<p><strong>' . $element['#title'] . '</strong><br>';
        $element['#markup'] .= t('Premise name');
        $element['#markup'] .= '<div class="hds-text-input__input-wrapper"><div class="hide-input form-text hds-text-input__input webform_large" type="text">&nbsp;</div></div>';
        $element['#markup'] .= t('Postal Code');
        $element['#markup'] .= '<div class="hds-text-input__input-wrapper"><div class="hide-input form-text hds-text-input__input webform_large" type="text">&nbsp;</div></div>';
        $element['#markup'] .= t('City owns the property');
        $element['#markup'] .= '<div class="hds-text-input__input-wrapper"><div class="hide-input form-text hds-text-input__input webform_large" type="text">&nbsp;</div></div>';
        $element['#markup'] .= '</p>';
      }
      // Get attachment descriptions from subfields.
      if ($element['#type'] === 'grants_attachments') {
        $element['#type'] = 'textfield';
        $element["#attributes"]["class"][] = 'hide-input';
        $element["#description__access"] = TRUE;
        if (!empty($element["#attachment__description"])) {
          $element['#description'] = $element["#attachment__description"];
        }
      }
      // Show no radios, hidden textfields.
      if ($element['#type'] === 'textarea' || $element['#type'] === 'textfield') {
        $element['#value'] = '';
      }
      if ($element['#type'] === 'hidden') {
        $element['#type'] = 'markup';
      }
      if ($element['#type'] === 'textarea') {
        $element['#type'] = 'markup';
        $element['#markup'] = '<p><strong>' . $element['#title'] . '</strong><br>';
        $element['#markup'] .= '<div class="hds-text-input__input-wrapper"><div class="hide-input form-text hds-text-input__input hds-text-input__textarea webform_large" type="text">&nbsp;</div></div>';
        if (isset($element['#description'])) {
          $element['#markup'] .= '<div>
 <div id="talousarvio--description" class="webform-element-description"><span>' . $element['#description'] . '</span></div>
    </div>';
          unset($element['#description']);
        }
      }
      if ($element['#type'] === 'textfield') {
        $element['#type'] = 'markup';
        $element['#markup'] = '<p><strong>' . $element['#title'] . '</strong><br>';
        $element['#markup'] .= '<div class="hds-text-input__input-wrapper"><div class="hide-input form-text hds-text-input__input webform_large" type="text">&nbsp;</div></div>';
        if (isset($element['#description'])) {
          $element['#markup'] .= '<div>
 <div id="talousarvio--description" class="webform-element-description"><span>' . $element['#description'] . '</span></div>
    </div>';
          unset($element['#description']);
        }
      }
      if ($element['#type'] === 'webform_section') {
        $element['#title_tag'] = 'h3';
      }
      if ($element['#type'] === 'select' || $element['#type'] === 'checkboxes' || $element['#type'] === 'radios') {
        $element['#type'] = 'markup';
        $element['#markup'] = '<p><strong>' . $element['#title'] . '</strong><br>';
        foreach ($element['#options'] as $key => $value) {
          $element['#markup'] .= '▢ ' . $value . '<br>';
        }
        $element['#markup'] .= '<br></p>';
      }
    }

    // Loop translated fields.
    if (!empty($translatedFields[$key])) {
      // Unset type since we do not want to override that from trans.
      unset($translatedFields[$key]['#type']);
      foreach ($translatedFields[$key] as $fieldName => $translatedValue) {
        // Replace with translated text. only if it's an string.
        if (isset($element[$fieldName]) && !is_array($translatedValue)) {
          $element[$fieldName] = $translatedValue;
        }
      }
    }
    return $element;
  }

}
