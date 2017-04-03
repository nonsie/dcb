<?php

namespace Drupal\dynoblock\Plugin\DynoField\TextArea;

use Drupal\dynoblock\Plugin\DynoField\DynoFieldBase;

/**
 * Provides a 'Textarea Field' DynoField Widget.
 *
 * @DynoField(
 *   id = "textarea_field",
 *   name = @Translation("Textarea Field"),
 * )
 */
class TextArea extends DynoFieldBase {

  public function form($properties = array()) {
    $field = $properties + array(
        '#type' => 'textarea',
      );
    $this->setFormElement($field);
    return $this->field;
  }

  public function render($value, $settings = array()) {
    if (!empty($value['value']) || (!empty($value) && is_string($value))) {
      $value_text = isset($value['value']) ? $value['value'] : $value;
      $this->filter($value_text);
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

  public function filter(&$text) {
    $filtered = preg_replace(array("^\[script(.*?)\]^", "^\[\/script(.*?)\]^"), array('<script${1}>', '</script>'), $text);
    if ($filtered) $text = $filtered;
  }

}
