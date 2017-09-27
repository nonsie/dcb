<?php

namespace Drupal\dcb\Plugin\DCBField\TextField;

use Drupal\dcb\Base\Field\DCBFieldBase;

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
   * @param array $values
   *
   * @return mixed
   */
  public function form(array $properties = [], $values = []) {
    $field = [
      '#type' => 'textfield',
      '#maxlength' => 256,
      '#title' => $properties['label'],
      '#default_value' => $values,
    ];

    return $field;
  }

  public function prepareStorage($values = []) {
    return $values;
  }

  /**
   * @param $properties
   * @param $values
   *
   * @return array|mixed
   * @internal param $value
   * @internal param array $settings
   */
  public function preRender($properties, $values) {
    if (!empty($values)) {
      return $values;
    }
    else {
      return [];
    }
  }

}
