<?php
/**
 * @File: Abstract class used to create a new Component theme. Components use
 * themes to define different display options with similar field sets.
 */

namespace Drupal\dcb;

/**
 * Class DCBComponentTheme.
 *
 * @package Drupal\dynoblock
 */
abstract class DCBComponentTheme {

  public $form_state = [];
  public $plugin;

  /**
   * DCBComponentTheme constructor.
   *
   * @param array $form_state
   * @param DCBComponentInterface $plugin
   */
  public function __construct(&$form_state = [], DCBComponentInterface $plugin) {
    $this->plugin = $plugin;
    $this->form_state = $form_state;
  }

  /**
   * This is used for adding theme specific form elements to the widgets form.
   *
   * @param $widget_form
   *  The widgets form
   * @param $settings
   *  an array of settings to be used in creating/adding form elements.
   */
  abstract public function form(&$widget_form, $settings = []);

  /**
   * Called when a widget using this theme needs displayed.
   *
   * @param $values
   *  and array of field values or $form_state['values']
   * @param $settings
   *  an array of settings to be used when building the widgets display.
   */
  abstract public function display($values = [], $settings = []);

  /**
   * Gets called when the widgets form is built.
   * This displays in the widget UI showing a preview image of the theme.
   *
   * @param $file
   *  a filename string that will be used to display its preview.
   * @return mixed|null
   */
  public function preview($file = '') {
    if ($file) {
      $file = file_create_url($this->plugin->directory . '/' . $file);
      $preview = [
        '#type' => 'markup',
        '#markup' => '<img src="' . $file . '"/>',
      ];
      return render($preview);
    }
  }
}
