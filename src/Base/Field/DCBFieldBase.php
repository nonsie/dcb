<?php

/**
 * @File: Base class for all DCBField plugins.
 */

namespace Drupal\dcb\Base\Field;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DCBFieldBase
 * @package Drupal\dcb\Plugin\DCBField
 */
abstract class DCBFieldBase extends PluginBase implements DCBFieldInterface, ContainerFactoryPluginInterface {

  protected $fileUsage;

  public function __construct(array $configuration, $pluginId, $pluginDefinition, FileUsageInterface $fileUsage) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->fileUsage = $fileUsage;
  }

  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('file.usage')
    );
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
   * @param array $values
   *
   * @return array
   */
  function prepareStorage($values = []) {
    return $values;
  }

  /**
   * Called when a widget using this field is being rendered.
   * @param $value
   *  An array containing the field values.
   * @param array $settings
   *  An array of settings that may be used in the building of the fields display.
   */
  public function preRender($properties, $values) {
    return $values;
  }

  /**
   * Called when a widget is saved.
   * - Note: this is not always called. The widget itself must call this explicitly.
   *    One use case example is when a widget has an image field.
   *    The widget would call this method so it could save the image permanently.
   * @internal param $value An array containing the field values.*  An array containing the field values.
   * @internal param $value an array containing the fields value(s) or $formState['values'].*  an array containing the fields value(s) or $formState['values'].
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

}
