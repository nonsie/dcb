<?php

namespace Drupal\dynoblock\DynoFields;

use Drupal\dynoblock\DynoField;

/**
 * Default Textfield.
 */
class DTextField extends DynoField {

  public function form($properties = array()) {
    $field = $properties + array(
        '#type' => 'textfield',
        '#maxlength' => 256,
      );
    $this->setFormElement($field);
    return $this->field;
  }

  public static function render($value, $settings = array()) {
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