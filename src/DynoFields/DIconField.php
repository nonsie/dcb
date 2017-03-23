<?php

namespace Drupal\dynoblock\DynoFields;

use Drupal\dynoblock\DynoFields\DTextField;

/**
 * Default Icon Textfield.
 */
class DIconField extends DTextField {

  public function form($properties = array()) {
    $field = $properties + array(
        '#type' => 'textfield',
        '#title' => t('Icon Class'),
      );
    $this->setFormElement($field);
    return $this->field;
  }

  public static function render($value, $settings = array()) {
    if (!empty($value)) {
      return $settings + array(
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => '',
          '#attributes' => array(
            'class' => array($value, 'DIconField'),
          ),
        );
    }
  }
}