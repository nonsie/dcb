<?php

namespace Drupal\dynoblock\Plugin\DynoField\Select;

use Drupal\dynoblock\Plugin\DynoField\DynoFieldBase;

/**
 * Provides a 'Select Field' DynoField Widget.
 *
 * @DynoField(
 *   id = "select_field",
 *   name = @Translation("Select Field"),
 * )
 */
class Select extends DynoFieldBase {

  public function form($properties = array()) {
    $field = $properties + array(
        '#type' => 'select',
      );
    $this->setFormElement($field);
    return $this->field;
  }

  public function render($value, $settings = array()) {
    if (!empty($value)) {
      kint($value);
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