<?php

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

  public function form($properties = array()) {
    $field = $properties + array(
        '#type' => 'text_format',
        '#format' => 'full_html',
        '#wysiwyg' => TRUE,
      );
    $this->setFormElement($field);
    return $this->field;
  }

  public function render($value, $settings = array()) {
    if (empty($value)) {
      return NULL;
    }
    $display = parent::render($value, $settings);
    $display['#type'] = 'markup';
    $display['#markup'] = check_markup($value['value'], 'full_html');
    $container = array(
      '#type' => 'container',
      'wysiwyg_content' => $display,
      '#attributes' => array(
        'class' => array('custom-text'),
      ),
    );
    return $container;
  }
}
