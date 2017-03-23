<?php

namespace Drupal\dynoblock\DynoFields;

use Drupal\dynoblock\DynoField;

/**
 * Default Select Field.
 */
class DSelectField extends DynoField {

  public function form($properties = array()) {
    $field = $properties + array(
        '#type' => 'select',
      );
    $this->setFormElement($field);
    return $this->field;
  }

  public static function render($value, $settings = array()) {
    if (!empty($value)) {
      return array(
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