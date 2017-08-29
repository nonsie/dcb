<?php

namespace Drupal\dcb\Plugin\DCBField\TextField;

use Drupal\dcb\Plugin\DCBField\DCBFieldBase;

/**
 * @File: Definition for Text Field DCB Plugin.
 */

/**
 * Provides a 'Text Field' DCBField Widget.
 *
 * @DCBField(
 *   id = "text_field",
 *   name = @Translation("Text Field"),
 * )
 */
class TextField extends DCBFieldBase {

  /**
   * @param array $properties
   *
   * @return mixed
   */
  public function form(array $properties = []) {
    $field = $properties + [
      '#type' => 'textfield',
      '#maxlength' => 256,
    ];
    $this->setFormElement($field);
    return $this->field;
  }

  /**
   * @param $value
   * @param array $settings
   */
  public static function render(&$value, array &$settings = []) {
    if (!empty($value)) {
      $value = $settings + [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $value,
        '#attributes' => [
          'class' => ['dyno-TextField'],
        ],
      ];
    }
  }

}
