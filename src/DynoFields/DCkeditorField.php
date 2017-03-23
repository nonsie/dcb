<?php

namespace Drupal\dynoblock\DynoFields;

use Drupal\dynoblock\DynoFields\DTextArea;

/**
 * Default wysiwyg ckeditor field class.
 */
class DCkeditorField extends DTextArea {
  public function form($properties = array()) {

    $field = $properties + array(
        '#type' => 'text_format',
        '#format' => 'full_html',
        '#wysiwyg' => TRUE,
      );
    $this->setFormElement($field);
    return $this->field;
  }

  public static function render($value, $settings = array()) {
    if (empty($value)) {
      return NULL;
    }
    $display = parent::render($value, $settings);
    $display['#type'] = 'markup';
    $display['#markup'] = $value['value'];
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