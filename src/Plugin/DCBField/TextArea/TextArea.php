<?php

namespace Drupal\dcb\Plugin\DCBField\TextArea;

use Drupal\dcb\Plugin\DCBField\DCBFieldBase;

/**
 * Provides a 'Textarea Field' DCBField Widget.
 *
 * @DCBField(
 *   id = "textarea_field",
 *   name = @Translation("Textarea Field"),
 * )
 */
class TextArea extends DCBFieldBase {

  /**
   * @param array $properties
   * @return mixed
   */
  public function form($properties = []) {
    $field = $properties + [
        '#type' => 'textarea',
      ];
    $this->setFormElement($field);
    return $this->field;
  }

  /**
   * @param $value
   * @param array $settings
   * @return array
   */
  public function render($value, $settings = []) {
    if (!empty($value['value']) || (!empty($value) && is_string($value))) {
      $value_text = isset($value['value']) ? $value['value'] : $value;
      $this->filter($value_text);
      return $settings + [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => token_replace($value_text),
          '#desctiption' => t('Use :script::/script: instead of <script></script> if you would like to add inline javascript.'),
          '#attributes' => [
            'class' => ['dyno-DTextArea'],
          ],
        ];
    }
  }

  /**
   * @param $text
   */
  public function filter(&$text) {
    $filtered = preg_replace([
      "^\[script(.*?)\]^",
      "^\[\/script(.*?)\]^"
    ], ['<script${1}>', '</script>'], $text);
    if ($filtered) {
      $text = $filtered;
    }
  }

}
