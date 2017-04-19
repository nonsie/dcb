<?php

/**
 * @File: Base class for Component plugins.
 */

namespace Drupal\dcb\Plugin\DCBComponent;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dcb\Form\ComponentWizardBaseForm;
use Drupal\dcb\Plugin\DCBField\DCBFieldInterface;
use Drupal\dcb\Plugin\DCBField\DCBFieldManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dcb\DCBComponentInterface;

/**
 * Class DCBComponentBase
 * @package Drupal\dcb\Plugin\DCBComponent
 */
class DCBComponentBase extends PluginBase implements DCBComponentInterface, ContainerFactoryPluginInterface {

  public $preview_image;
  public $module;
  public $class;
  public $dir;
  public $properties;
  public $form;
  public $form_state;
  public $themes;
  public $namespace;
  public $dcbFieldManager;
  public $fields;
  public $output;
  public $layout;
  public $outerId;
  /**
   * @var ComponentWizardBaseForm
   */
  public $componentform;

  /**
   * DCBComponentBase constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param DCBFieldManager $dcbFieldManager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DCBFieldManager $dcbFieldManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $properties = $this->pluginDefinition['properties'];
    $this->module = $properties['module'];
    $this->class = $this->getClass();
    $this->dir = drupal_get_path('module', $this->module);
    $this->themes = $this->getThemes();
    $this->preview_image = $this->getPreviewImageFilePath($properties['preview_image']);
    $this->properties = $properties;
    $this->namespace = $this->getNamespace();
    $this->dcbFieldManager = $dcbFieldManager;
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
      $container->get('plugin.manager.dcbfield')
    );
  }

  /**
   * @param $id
   * @param bool $init
   * @param array $form_state
   * @return mixed|null|object
   */
  public function getField($id, $init = FALSE, $form_state = []) {
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
   * @param DCBFieldInterface $field
   * @return object
   */
  public function initField($field) {
    return $this->dcbFieldManager->createInstance($field['id']);
  }

  /**
   * @return array|\mixed[]|null
   */
  public function loadFields() {
    return $this->fields = $this->dcbFieldManager->getDefinitions();
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
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function build(ComponentWizardBaseForm $componentform, array $values) {
    $this->componentform = $componentform;
    $this->outerId = $this->componentform->randId();

    $this->form['fields'] = [
      '#type' => 'container',
        '#tree' => TRUE,
        '#attributes' => [
          'id' => $this->outerId,
        ],
    ] + $this->outerForm(!empty($values['fields']) ? $values['fields'] : []);
  }


  /**
   * @param $values
   * @return null
   */
  public function outerForm($values) {
    return NULL;
  }

  /**
   * @param array $form_state
   * @param $items
   * @param $delta
   * @return mixed
   */
  public function widgetForm(&$form_state = [], $items, $delta) {
    $container_id = $this->componentform->randId();
    $element['items'] = [
        '#type' => 'details',
        '#title' => t('Item @delta', [
          '@delta' => ($delta + 1),
        ]),
        '#open' => $this->getWidgetDetailsState($form_state),
        '#collapsible' => TRUE,
        '#attributes' => [
          'id' => $container_id,
        ],
      ] + $this->repeatingFields(!empty($items[$delta]) ? $items[$delta] : [], $delta, $container_id);
    return $element;
  }


  /**
   * @param array $values
   * @param $delta
   * @param $container_id
   * @return null
   */
  public function repeatingFields($values = [], $delta, $container_id) {
    return NULL;
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
    return ['return_element' => $form[$this->getId()][$trigger['#attributes']['delta']]];
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxSubmit() {

  }

  /**
   * @param $values
   * @return mixed
   */
  public function preRender($values) {
    $this->form_state = $values;
    $theme = !empty($this->themes[$values['theme']]['handler']) ? $this->themes[$values['theme']]['handler'] : NULL;
    if ($theme = $this->loadTheme($theme)) {
      $this->output = $theme->display($values);
    }
    return $this->output;
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
    if ($theme) {
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
    if (is_object($form_state)) {
      $trigger = $form_state->getTriggeringElement();
    }
    if (is_object($form_state) && isset($trigger['#attributes']['delta'])) {
      if ($trigger_delta == $delta) {
        $open = TRUE;
      }
    }
    return $open;
  }

}
