<?php

namespace Drupal\dynoblock;

abstract class DynoField {
  public $form_state = array();
  public function __construct(&$form_state) {
    $this->form_state = $form_state;
    $this->field = array(
      'handler' => array(
        '#type' => 'hidden',
        '#value' => get_called_class(),
      ),
    );
  }
  /**
   * Method for creating fields.
   * @see https://api.drupal.org/api/drupal/developer!topics!forms_api_reference.html/7
   * @param $properties
   *  An array of field properties. eg: array('#title' => t('Link'))
   * @return $field
   *  A form render array.
   */
  abstract public function form($properties = array());

  /**
   * Called when a widget using this field is being rendered.
   * @param $value
   *  An array containing the field values.
   * @param $settings
   *  An array of settings that may be used in the building of the fields display.
   * @return $display
   *  An render array containing the fields output.
   */
  public static function render($value, $settings = array()) {}

  /**
   * Called whena widget is saved.
   * - Note: this is not always called. The widget itself must call this excplicitly.
   *    One use case example is when a widget has an image field.
   *    The widget would call this method so it could save the image perminantly.
   * @param $value
   *  An array containing the field values.
   * @param $value
   *  an array containing the fields value(s) or $form_state['values'].
   */
  public function onSubmit($value) {}
  public function onAjax($form, &$form_state) {}
  public function validate($element) {}
  protected function setFormElement(array $values) {
    $this->field['value'] = $values;
  }
}