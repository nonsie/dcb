<?php

namespace Drupal\dcb;

/**
 * @File: Abstract class used to create a new Component theme. Components use
 * themes to define different display options with similar field sets.
 */

/**
 * Class DCBComponentTheme.
 *
 * @package Drupal\dcb
 */
abstract class DCBComponentTheme {

  /**
   * @var array
   */
  public $formState = [];

  /**
   * @var \Drupal\dcb\DCBComponentInterface
   */
  public $plugin;

  /**
   * DCBComponentTheme constructor.
   *
   * @param array $formState
   * @param DCBComponentInterface $plugin
   */
  public function __construct(&$formState = [], DCBComponentInterface $plugin) {
    $this->plugin = $plugin;
    $this->formState = $formState;
  }

  /**
   * This is used for adding theme specific form elements to the widgets form.
   *
   * @param $widget_form
   *   The widgets form
   * @param $settings
   *   An array of settings to be used in creating/adding form elements.
   */
  abstract public function form(&$widget_form, $settings = []);

  /**
   * Called when a widget using this theme needs displayed.
   *
   * @param $values
   *   An array of field values or $formState['values']
   * @param $settings
   *   An array of settings to be used when building the widgets display.
   */
  abstract public function display($values = [], $settings = []);

  /**
   * Gets called when the widgets form is built.
   *
   * This displays in the widget UI showing a preview image of the theme.
   *
   * @param $file
   *   A filename string that will be used to display its preview.
   *
   * @return mixed|null
   *   Return the theme preview.
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
