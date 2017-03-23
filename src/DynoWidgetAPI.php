<?php

namespace Drupal\dynoblock;

class DynoWidgetAPI {

  public static $field;

  public static function element(&$form_state, $type, $properties = array()) {
    $field = new $type($form_state);
    self::$field = $field;
    return $field->form($properties);
  }

  public static function display($field, $settings = array()) {
    if (!empty($field['handler'])) {
      return $field['handler']::render($field['value'], $settings);
    }
  }

  public static function themeSettings() {
    return array(
      'theme_settings' => array(
        '#type' => 'container',
        '#weight' => -97,
        '#tree' => TRUE,
        '#attributes' => array(
          'class' => array('dyno-theme-settings'),
        ),
      ),
    );
  }

}