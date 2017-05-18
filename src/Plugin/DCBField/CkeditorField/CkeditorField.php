<?php

/**
 * @File: Defines the DCB CKEditor field.
 */

namespace Drupal\dcb\Plugin\DCBField\CkeditorField;

use Drupal\dcb\Plugin\DCBField\DCBFieldBase;

/**
 * Provides a 'Ckeditor Field' DCBField Widget.
 *
 * @DCBField(
 *   id = "ckeditor_field",
 *   name = @Translation("Ckeditor Field"),
 * )
 */
class CkeditorField extends DCBFieldBase {

  /**
   * @param array $properties
   * @return mixed
   */
  public function form($properties = []) {
    $field = $properties + [
        '#type' => 'text_format',
        '#format' => 'full_html',
        '#wysiwyg' => TRUE,
      ];
    $this->setFormElement($field);
    return $this->field;
  }

  /**
   * @param $value
   * @param array $settings
   * @return array|null
   */
  public static function preRender(&$value, &$settings = []) {
    if (empty($value)) {
      return NULL;
    }
    $display = parent::preRender($value, $settings);
    $display['#type'] = 'markup';
    $display['#markup'] = check_markup($value['value'], 'full_html');
    $container = [
      '#type' => 'container',
      'wysiwyg_content' => $display,
      '#attributes' => [
        'class' => ['custom-text'],
      ],
    ];
    $value = $container;
  }
}
