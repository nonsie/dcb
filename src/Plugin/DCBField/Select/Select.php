<?php

/**
 * @File: Plugin definition for DCBField select plugin.
 */

namespace Drupal\dcb\Plugin\DCBField\Select;

use Drupal\dcb\Base\Field\DCBFieldBase;

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
  public function form(array $properties = []) {
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
  public static function preRender(&$value, &$settings = []) {
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
