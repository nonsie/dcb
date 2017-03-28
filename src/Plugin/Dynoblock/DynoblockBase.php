<?php

namespace Drupal\dynoblock\Plugin\Dynoblock;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dynoblock\DynoFieldManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dynoblock\DynoblockInterface;

class DynoblockBase extends PluginBase implements DynoblockInterface, ContainerFactoryPluginInterface {

  public $preview_image;
  public $module;
  public $class;
  public $dir;
  public $properties;
  public $dynoFieldManager;
  public $fields;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, DynoFieldManager $dynoFieldManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $properties = $this->pluginDefinition['properties'];
    $this->module = $properties['module'];
    $this->class = $this->getClass();
    $this->dir = drupal_get_path('module', $this->module);
    $this->themes = $this->getThemes();
    $this->preview_image = $this->getPreviewImageFilePath($properties['preview_image']);
    $this->properties = $properties;
    $this->namespace = $this->getNamespace();
    $this->dynoFieldManager = $dynoFieldManager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.dynofield')
    );
  }

  /**
   * @param $id
   * @return null
   */
  public function getField($id) {
    $fields = $this->loadFields();
    return array_key_exists($id, $fields) ? $fields[$id] : NULL;
  }

  /**
   * @return array|\mixed[]|null
   */
  public function loadFields() {
    return $this->fields = $this->dynoFieldManager->getDefinitions();
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
  public function getNamespace() {
    return __NAMESPACE__;
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

  /**
   * {@inheritdoc}
   */
  public function loadTheme($theme) {

  }

}
