<?php

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

  public function form($properties = array()) {
    $field = $properties + array(
        '#type' => 'select',
      );
    $this->setFormElement($field);
    return $this->field;
  }

  public function render($value, $settings = array()) {
    if (!empty($value)) {
      return $settings + array(
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $value,
        '#attributes' => array(
          'class' => array('dyno-TextField'),
        ),
      );
    }
  }
}