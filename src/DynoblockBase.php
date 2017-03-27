<?php

namespace Drupal\dynoblock;

use Drupal\Component\Plugin\PluginBase;

class DynoblockBase extends PluginBase implements DynoblockInterface {

  public $preview_image;
  public $module;
  public $class;
  public $dir;
  public $properties;

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $properties = $this->pluginDefinition['properties'];
    $this->module = $properties['module'];
    $this->class = $this->getClass();
    $this->dir = drupal_get_path('module', $this->module);
    $this->preview_image = $this->getPreviewImageFilePath($properties['preview_image']);
    $this->properties = $properties;
  }

  /**
   *
   */
  private function getPreviewImageFilePath($uri) {
    return file_create_url(drupal_get_path('module', $this->module) . '/' . $uri);
  }

  /**
   * {@inheritdoc}
   */
  public function getShortDesc() {
    return $this->pluginDefinition['description_short'];
  }

  /**
   * {@inheritdoc}
   */
  public function getClass() {
    return $this->pluginDefinition['class'];
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->pluginDefinition['properties'];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->pluginDefinition['name'];
  }

  /**
   * {@inheritdoc}
   */
  public function getThemes() {
    return $this->pluginDefinition['themes'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultTheme() {
    return $this->pluginDefinition['default_theme'];
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    return "init";
  }

  /**
   * {@inheritdoc}
   */
  public function build($form_state = array()) {

  }

  /**
   * {@inheritdoc}
   */
  public function widgetForm(&$form_state = array(), $items, $delta) {

  }

  /**
   * {@inheritdoc}
   */
  public function formSubmit(&$form_state) {

  }


  /**
   * {@inheritdoc}
   */
  public function ajaxCallback() {

  }

  /**
   * {@inheritdoc}
   */
  public function ajaxSubmit() {

  }

  /**
   * {@inheritdoc}
   */
  public function preRender($values) {

  }

  /**
   * {@inheritdoc}
   */
  public function render() {

  }

}
