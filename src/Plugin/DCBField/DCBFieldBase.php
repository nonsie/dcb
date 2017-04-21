<?php

/**
 * @File: Base class for all DCBField plugins.
 */

namespace Drupal\dcb\Plugin\DCBField;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DCBFieldBase
 * @package Drupal\dcb\Plugin\DCBField
 */
class DCBFieldBase extends PluginBase implements DCBFieldInterface, ContainerFactoryPluginInterface {

  public $form_state = [];
  public $field;
  public $display;
  public $fileUsage;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, FileUsageInterface $fileUsage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileUsage = $fileUsage;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('file.usage')
    );
  }

  /**
   * @param $form_state
   */
  public function init($form_state) {
    $this->form_state = $form_state;
    $this->field = [
      'handler' => [
        '#type' => 'hidden',
        '#value' => get_called_class(),
      ],
    ];
  }

  /**
   * Return the id of the Widget.
   *
   * @return string
   */
  function getId() {
    return $this->pluginDefinition['id'];
  }

  /**
   * Return the name of the Widget.
   *
   * @return string
   */
  function getName() {
    return $this->pluginDefinition['name'];
  }

  /**
   * Method for creating fields.
   * @see https://api.drupal.org/api/drupal/developer!topics!forms_api_reference.html/7
   * @param array $properties
   *  An array of field properties. eg: array('#title' => t('Link'))
   */
  function form($properties = []) {
  }

  /**
   * Called when a widget using this field is being rendered.
   * @param $value
   *  An array containing the field values.
   * @param array $settings
   *  An array of settings that may be used in the building of the fields display.
   */
  function render($value, $settings = []) {
  }

  /**
   * Called when a widget is saved.
   * - Note: this is not always called. The widget itself must call this explicitly.
   *    One use case example is when a widget has an image field.
   *    The widget would call this method so it could save the image permanently.
   * @internal param $value An array containing the field values.*  An array containing the field values.
   * @internal param $value an array containing the fields value(s) or $form_state['values'].*  an array containing the fields value(s) or $form_state['values'].
   * @param $value
   * @param $bid
   */
  function onSubmit($value, $bid) {
  }

  /**
   *
   */
  function onAjax() {
  }

  /**
   *
   */
  function validate() {
  }

  /**
   * @param array $values
   * @return mixed|void
   */
  function setFormElement($values = []) {
    $this->field['value'] = $values;
  }

}