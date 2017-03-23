<?php

namespace Drupal\dynoblock\DynoFields;

use Drupal\dynoblock\DynoField;

class DTextArea extends DynoField {

  public function form($properties = array()) {
    $field = $properties + array(
        '#type' => 'textarea',
      );
    $this->setFormElement($field);
    return $this->field;
  }

  public static function render($value, $settings = array()) {
    if (!empty($value['value']) || (!empty($value) && is_string($value))) {
      $value_text = isset($value['value']) ? $value['value'] : $value;
      self::filter($value_text);
      return $settings + array(
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => token_replace($value_text),
          '#desctiption' => t('Use :script::/script: instead of <script></script> if you would like to add inline javascript.'),
          '#attributes' => array(
            'class' => array('dyno-DTextArea'),
          ),
        );
    }
  }

  public static function filter(&$text) {
    $filtered = preg_replace(array("^\[script(.*?)\]^", "^\[\/script(.*?)\]^"), array('<script${1}>', '</script>'), $text);
    if ($filtered) $text = $filtered;
  }
}