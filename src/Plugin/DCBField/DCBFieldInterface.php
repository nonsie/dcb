<?php

/**
 * @File: Interface for DCBField plugin type.
 */

namespace Drupal\dcb\Plugin\DCBField;


use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface DCBFieldInterface
 * @package Drupal\dcb\Plugin\DCBField
 */
interface DCBFieldInterface extends PluginInspectionInterface {

  /**
   * Return the id of the Widget.
   *
   * @return string
   */
  function getId();

  /**
   * Return the name of the Widget.
   *
   * @return string
   */
  function getName();

  /**
   * Method for creating fields.
   * @see https://api.drupal.org/api/drupal/developer!topics!forms_api_reference.html/7
   * @param $properties
   *  An array of field properties. eg: array('#title' => t('Link'))
   * @return $field
   *  A form render array.
   */
  function form($properties = []);

  /**
   * Called when a widget using this field is being rendered.
   * @param $value
   *  An array containing the field values.
   * @param $settings
   *  An array of settings that may be used in the building of the fields display.
   * @return $display
   *  An render array containing the fields output.
   */
  public static function preRender(&$value, &$settings = []);

  /**
   * Called when a widget is saved.
   * - Note: this is not always called. The widget itself must call this explicitly.
   *    One use case example is when a widget has an image field.
   *    The widget would call this method so it could save the image permanently.
   * @param $value
   * @param $bid
   * @return
   * @internal param $value An array containing the field values.*  An array containing the field values.
   * @internal param $value an array containing the fields value(s) or $form_state['values'].*  an array containing the fields value(s) or $form_state['values'].
   */
  function onSubmit($value, $bid);

  /**
   * @return mixed
   */
  function onAjax();

  /**
   * @return mixed
   */
  function validate();

  /**
   * @param $values
   * @return mixed
   */
  function setFormElement($values);

}