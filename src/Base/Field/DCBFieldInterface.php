<?php

namespace Drupal\dcb\Base\Field;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface for DCBField plugin type.
 *
 * @File: Interface for DCBField plugin type.
 */

/**
 * Interface DCBFieldInterface.
 *
 * @package Drupal\dcb\Plugin\DCBField
 */
interface DCBFieldInterface extends PluginInspectionInterface {

  /**
   * Return the ID of the Widget.
   *
   * @return string
   *   Return id.
   */
  public function getId();

  /**
   * Return the name of the Widget.
   *
   * @return string
   */
  public function getName();

  /**
   * Method for creating fields.
   *
   * @param $properties
   *   An array of field properties. eg: array('#title' => t('Link'))
   *
   * @see https://api.drupal.org/api/drupal/developer!topics!forms_api_reference.html/7
   *
   * @return mixed
   *   A form render array.
   */
  public function form(array $properties = []);

  /**
   * Called when a widget using this field is being rendered.
   *
   * @param $value
   *   An array containing the field values.
   * @param $settings
   *   An array of settings that may be used in the building of the fields
   *   display.
   *
   * @return mixed
   *   A render array containing the fields output.
   */
  public static function preRender(&$value, &$settings = []);

  /**
   * Called when a widget is saved.
   *
   * - Note: this is not always called. The widget itself must call this
   * explicitly.
   *   One use case example is when a widget has an image field.
   *   The widget would call this method so it could save the image permanently.
   *
   * @param $value
   * @param $bid
   *
   * @return
   *
   * @internal param $value An array containing the field values.*  An array containing the field values.
   * @internal param $value an array containing the fields value(s) or $formState['values'].*  an array containing the fields value(s) or $formState['values'].
   */
  public function onSubmit($value, $bid);

  /**
   * @return mixed
   */
  public function onAjax();

  /**
   * @return mixed
   */
  public function validate();

  /**
   * @param $values
   *
   * @return mixed
   */
  public function setFormElement($values);

}
