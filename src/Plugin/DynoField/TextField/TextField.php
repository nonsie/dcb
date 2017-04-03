<?php

namespace Drupal\dynoblock\Plugin\DynoField\TextField;

use Drupal\dynoblock\Plugin\DynoField\DynoFieldBase;

/**
 * Provides a 'Text Field' DynoField Widget.
 *
 * @DynoField(
 *   id = "text_field",
 *   name = @Translation("Text Field"),
 * )
 */
class TextField extends DynoFieldBase {

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
