<?php

namespace Drupal\dynoblock\Plugin\DynoField;

use Drupal\dynoblock\DynoFieldBase;

/**
 * Provides a 'Ckeditor Field' DynoField Widget.
 *
 * @DynoField(
 *   id = "ckeditor_field",
 *   name = @Translation("Ckeditor Field"),
 * )
 */
class CkeditorField extends DynoFieldBase {

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
