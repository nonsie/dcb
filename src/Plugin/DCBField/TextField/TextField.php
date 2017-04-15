<?php

namespace Drupal\dcb\Plugin\DCBField\TextField;

use Drupal\dcb\Plugin\DCBField\DCBFieldBase;

/**
 * Provides a 'Text Field' DCBField Widget.
 *
 * @DCBField(
 *   id = "text_field",
 *   name = @Translation("Text Field"),
 * )
 */
class TextField extends DCBFieldBase {

  public function form($properties = array()) {
    $field = $properties + array(
        '#type' => 'textfield',
        '#maxlength' => 256,
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
