<?php

namespace Drupal\dcb\Base\Component;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dcb\Manager\DCBFieldManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DCBComponentBase.
 *
 * @File: Base class for Component plugins.
 *
 * @package Drupal\dcb\Plugin\DCBComponent
 */
abstract class DCBComponentBase extends PluginBase implements ContainerFactoryPluginInterface, DCBComponentInterface {

  private $instanceData;

  /**
   * DCBComponentBase constructor.
   *
   * @param array $configuration
   * @param string $pluginId
   * @param mixed $pluginDefinition
   * @param DCBFieldManager $dcbFieldManager
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param string $pluginId
   * @param mixed $pluginDefinition
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition
    );
  }

  public function getComponentTypeId() {
    return $this->pluginId;
  }

  public function getComponentTypeName() {
    return $this->pluginDefinition['name'];
  }

  public function getComponentTypeDescription($type = 'short') {
    if ($type == 'short') {
      return $this->pluginDefinition['descriptionShort'];
    }
    else {
      return $this->pluginDefinition['description'];
    }
  }

  public function getComponentTypeDisplayOptions() {
    return $this->pluginDefinition['displayOptions'];
  }

  public function getComponentDefaultDisplayOption() {
    return $this->pluginDefinition['defaultDisplayOption'];
  }

  public function getComponentItemCardinalitySettings() {
    return $this->pluginDefinition['formSettings']['cardinality'];
  }

  public function setInstanceData(array $data) {
    $this->instanceData = $data;
  }

  public function getInstanceData() {
    return $this->instanceData;
  }

  public function getPreviewImageWebPath() {
    $path = drupal_get_path('module', $this->pluginDefinition['provider']);
    return '/' . $path . '/src/Plugin/DCBComponent/' . $this->getComponentTypeId() . '/images';
  }

  public function getComponentPreviewImage() {
   return $this->getPreviewImageWebPath() . '/' . $this->getComponentTypeName() . '.png';
  }

  public function getOuterFieldsDefinition() {
    return $this->pluginDefinition['fieldSets']['outer']['fields']['visible'];
  }

  public function getOuterFieldsInstanceData(string $key) {
    if (isset($this->getInstanceData()['fieldSets']['outer']['fields']['visible'][$key]['field_data'])
        && !empty($this->getInstanceData()['fieldSets']['outer']['fields']['visible'][$key]['field_data'])) {
      return $this->getInstanceData()['fieldSets']['outer']['fields']['visible'][$key]['field_data'];
    }
    else {
      return [];
    }
  }

  public function preRender(array $data) {
    return $data;
  }

  public function getFieldProperties(string $key) {
    $registered_fields = $this->register();
    return isset($registered_fields[$key]) ? $registered_fields[$key] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function formSubmit(FormStateInterface $formState) {

  }

}
