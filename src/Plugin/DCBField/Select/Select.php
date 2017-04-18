<?php

/**
 * @File: Plugin definition for DCBField select plugin.
 */

namespace Drupal\dcb\Plugin\DCBField\Select;

use Drupal\dcb\Plugin\DCBField\DCBFieldBase;

/**
 * Provides a 'Select Field' DCBField Widget.
 *
 * @DCBField(
 *   id = "select_field",
 *   name = @Translation("Select Field"),
 * )
 */
class Select extends DCBFieldBase {

  /**
   * @param array $properties
   * @return mixed
   */
  public function form($properties = []) {
    $field = $properties + [
        '#type' => 'select',
      ];
    $this->setFormElement($field);
    return $this->field;
  }

  /**
   * @param $value
   * @param array $settings
   * @return array
   */
  public function render($value, $settings = []) {
    if (!empty($value)) {
      return $settings + [
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