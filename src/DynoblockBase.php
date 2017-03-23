<?php

namespace Drupal\dynoblock;

use Drupal\Component\Plugin\PluginBase;

class DynoblockBase extends PluginBase implements DynoblockInterface {

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
  public function preRender() {

  }

  /**
   * {@inheritdoc}
   */
  public function render() {

  }

}
