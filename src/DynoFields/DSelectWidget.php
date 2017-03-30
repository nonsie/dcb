<?php

namespace Drupal\dynoblock\DynoFields;

use Drupal\dynoblock\DynoField;

/**
 * Default Widget Selection Field.
 */
class DSelectWidget extends DynoField {

  public function form($properties = array()) {
    $container_id = DynoBlockForms::randId();
    $field['widget_form'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'id' => $container_id,
      ),
    );
    $field['widget'] = array(
      '#type' => 'select',
      '#title' => t('Widget'),
      '#weight' => -100,
      '#default_value' => !empty($properties['#default_value']['widget']) ? $properties['#default_value']['widget'] : NULL,
      '#ajax' => array(
        'wrapper' => $container_id,
        'callback' => DynoBlockForms::fieldWidgetAjaxCallback,
        'handler' => get_called_class(),
      ),
      '#options' => self::loadWidgets(),
    );
    if (!empty($properties['#default_value']['widget'])) {
      $field['widget_form']['widget'] = $this->buildWIdgetForm($properties['#default_value']['widget'], $properties['#default_value']['widget_form']);
    }
    $this->setFormElement($field);
    return $this->field;
  }

  private static function loadWidgets() {
    $widgets = array();
    if (!empty(DynoBlocks::$widgets)) {
      foreach (DynoBlocks::$widgets as $key => $widget) {
        $widgets[$key] = $widget['label'];
      }
    }
    array_unshift($widgets, t('- Select Widget -'));
    return $widgets;
  }

  public static function getWidget($name) {
    if (!empty(DynoBlocks::$widgets)) {
      if (array_key_exists($name, DynoBlocks::$widgets)) return DynoBlocks::$widgets[$name];
    }
  }

  public function buildWIdgetForm($widget, $default_values) {
    $core = \Drupal::service('dynoblock.core');
    $form = array();
    if ($widget = self::getWidget($widget)) {
      if ($widget_form = $core->initPlugin($widget['id'])) {
        $widget_form->init()->build($default_values['widget']);
        DynoBlockForms::buildThemeSelection($widget, $widget_form, $default_values['widget']);
        $cardinality = isset($widget['form_settings']['cardinality']) ? $widget['form_settings']['cardinality'] : NULL;
        if ($cardinality === NULL) {
          return $widget_form->form;
        }
        else {
          DynoBlockForms::buildWidgetForm($widget, $widget_form, $default_values['widget']);
        }
        $form = $widget_form->form;
      }
    }
    return $form;
  }

  public function onAjax($form, &$form_state) {
    $item = $form;
    if (!empty($form_state['triggering_element']['#parents'])) {
      foreach ($form_state['triggering_element']['#parents'] as $parent) {
        $item = $item[$parent];
        if ($parent === 'value') {
          break;
        }
      }
    }
    return array('return_element' => $item['widget_form']);
  }

  public static function render($value, $settings = array()) {
    return NULL; // #TODO: Logic that gets all selected widgets and renders them.
  }
}
