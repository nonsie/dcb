<?php

namespace Drupal\dynoblock\Plugin\Dynoblock;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dynoblock\Form\ComponentWizardBaseForm;
use Drupal\dynoblock\Plugin\DynoField\DynoFieldInterface;
use Drupal\dynoblock\Plugin\DynoField\DynoFieldManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dynoblock\DynoblockInterface;

class DynoblockBase extends PluginBase implements DynoblockInterface, ContainerFactoryPluginInterface {

  public $preview_image;
  public $module;
  public $class;
  public $dir;
  public $properties;
  public $form;
  public $form_state;
  public $themes;
  public $namespace;
  public $dynoFieldManager;
  public $fields;
  public $output;

  /**
   * DynoblockBase constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param DynoFieldManager $dynoFieldManager
   */
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

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @return static
   */
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
   * @param bool $init
   * @return mixed|null|object
   */
  public function getField($id, $init = FALSE, $form_state = array()) {
    $fields = $this->loadFields();
    $field = array_key_exists($id, $fields) ? $fields[$id] : NULL;
    if ($field) {
      if ($init) {
        $newfieldinstance = $this->initField($field);
        $newfieldinstance->init($form_state);
        return $newfieldinstance;
      }
      return $field;
    }
  }

  /**
   * @param DynoFieldInterface $field
   * @return object
   */
  public function initField($field) {
    return $this->dynoFieldManager->createInstance($field['id']);
  }

  /**
   * @return array|\mixed[]|null
   */
  public function loadFields() {
    return $this->fields = $this->dynoFieldManager->getDefinitions();
  }

  /**
   * @param $uri
   * @return string
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
    return $this->pluginId;
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
  public function build(ComponentWizardBaseForm $componentform, array $values) {

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
  public function ajaxCallback($form, FormStateInterface &$form_state) {
    $trigger = $form_state->getTriggeringElement();
    // this returnes a group of fields after an extra field is selected in the UI.
    return array('return_element' => $form[$this->getId()][$trigger['#attributes']['delta']]);
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
    if($theme) {
      return $this->initTheme($this->pluginId, $theme);
    }
  }

  /**
   * @param $pluginId
   * @param $theme
   * @return mixed
   */
  public function initTheme($pluginId, $theme) {
    $theme = $this->namespace . '\\' . $pluginId . '\\' . $theme;
    return new $theme($this->form_state, $this);
  }

  /**
   * @param $form_state
   * @return bool
   */
  public function getWidgetDetailsState($form_state) {
    $open = FALSE;
    if(is_object($form_state)) $trigger = $form_state->getTriggeringElement();
    if (is_object($form_state) && isset($trigger['#attributes']['delta'])) {
      if ($trigger_delta == $delta) {
        $open = TRUE;
      }
    }
    return $open;
  }

}
