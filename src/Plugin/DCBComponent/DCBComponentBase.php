<?php

/**
 * @File: Base class for Component plugins.
 */

namespace Drupal\dcb\Plugin\DCBComponent;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dcb\Form\ComponentWizardBaseForm;
use Drupal\dcb\Plugin\DCBField\DCBFieldManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dcb\DCBComponentInterface;

/**
 * Class DCBComponentBase
 * @package Drupal\dcb\Plugin\DCBComponent
 */
abstract class DCBComponentBase extends PluginBase implements DCBComponentInterface, ContainerFactoryPluginInterface {

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
  public $form_settings;
  public $parent_theme;
  public $rebuild;
  public $default_theme;
  public $ItemThemes;
  public $InnerFieldOptions;
  public $OuterFieldOptions;
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
    $this->properties = $this->pluginDefinition['properties'];
    $this->module = $this->properties['module'];
    $this->class = $this->getClass();
    $this->dir = drupal_get_path('module', $this->module);
    $this->themes = $this->getThemes();
    $this->preview_image = $this->getPreviewImageFilePath($this->properties['preview_image']);
    $this->namespace = $this->getNamespace();
    $this->dcbFieldManager = $dcbFieldManager;
    $this->form_settings = $this->pluginDefinition['form_settings'];
    $this->parent_theme = isset($this->pluginDefinition['parent_theme']) ? $this->pluginDefinition['parent_theme'] : [];
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
   * {@inheritdoc}
   */
  public function getField($id, bool $init = FALSE, FormStateInterface $form_state = NULL) {
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
   * {@inheritdoc}
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
    return 'Drupal\\' . $this->module . '\Plugin\DCBComponent';
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
  public function getOuterForm(ComponentWizardBaseForm $componentform, array $values) {
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
   * {@inheritdoc}
   */
  public function outerForm($values) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getRepeatingFields(&$form_state = [], $items, $delta) {
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
      ] + $this->repeatingFields(!empty($items[$delta]) ? $items[$delta] : [], $delta);
    return $element;
  }


  /**
   * {@inheritdoc}
   */
  public function repeatingFields($values = [], $delta) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function formSubmit(FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function ajaxCallback($form, FormStateInterface &$form_state) {
    $trigger = $form_state->getTriggeringElement();
    // This returns a group of fields after an extra field is selected in the UI.
    return ['return_element' => $form[$this->getId()][$trigger['#attributes']['delta']]];
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxSubmit() {

  }

  /**
   * {@inheritdoc}
   */
  public function preRender($form_values) {
    $this->form_state = $form_values;

    if (!empty($form_values['fields'])) {
      $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($form_values['fields']), \RecursiveIteratorIterator::LEAVES_ONLY);
      foreach ($iterator as $key => $value) {
        if ($key == 'handler') {
          $keys = [];
          for ($i = $iterator->getDepth() - 1; $i >= 0; $i--) {
            $keys[] = $iterator->getSubIterator($i)->key();
          }
          $keys = array_reverse($keys);
          $array_list[] = $keys;
        }
      }

      if (isset($array_list) && !empty($array_list)) {
        foreach ($array_list as $array_item) {
          $field_base_array = NestedArray::getValue($form_values['fields'], $array_item);
          /* @var $field_base_array \Drupal\dcb\Plugin\DCBField\DCBFieldInterface */
          $field_base_array['handler']::preRender($field_base_array['value']);
          $array_item[] = 'value';
          NestedArray::setValue($form_values['fields'], $array_item, $field_base_array['value']);
        }
      }
    }

    $iterator = NULL;
    $key = NULL;
    $value = NULL;
    $array_list = NULL;

    if (!empty($form_values[$form_values['widget']])) {
      $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($form_values[$form_values['widget']]), \RecursiveIteratorIterator::LEAVES_ONLY);
      foreach ($iterator as $key => $value) {
        if ($key == 'handler') {
          $keys = [];
          for ($i = $iterator->getDepth() - 1; $i >= 0; $i--) {
            $keys[] = $iterator->getSubIterator($i)->key();
          }
          $keys = array_reverse($keys);
          $array_list[] = $keys;
        }
      }

      if (isset($array_list) && !empty($array_list)) {
        foreach ($array_list as $array_item) {
          $field_base_array = NestedArray::getValue($form_values[$form_values['widget']], $array_item);
          /* @var $field_base_array \Drupal\dcb\Plugin\DCBField\DCBFieldInterface */
          $field_base_array['handler']::preRender($field_base_array['value']);
          $array_item[] = 'value';
          NestedArray::setValue($form_values[$form_values['widget']], $array_item, $field_base_array['value']);
        }
      }
    }

    $theme = !empty($this->themes[$form_values['theme']]['handler']) ? $this->themes[$form_values['theme']]['handler'] : NULL;
    if ($theme = $this->loadTheme($theme)) {
      $this->output = $theme->display($form_values);
    }
    return $this->output;
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
   * {@inheritdoc}
   */
  public function initTheme($pluginId, $theme) {
    $theme = $this->namespace . '\\' . $pluginId . '\\' . $theme;
    return new $theme($this->form_state, $this);
  }

  /**
   * @param $form_state
   * @return bool
   */
  public function getWidgetDetailsState(FormStateInterface $form_state) {
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

  /**
   * {@inheritdoc}
   */
  public function registerItemThemeOptions($options) {
    $key = array_keys($options);
    $this->ItemThemes[$key[0]] = $options[$key[0]];
  }

  /**
   * {@inheritdoc}
   */
  public function registerInnerFieldOptions($field_info) {
    $key = array_keys($field_info);
    $this->InnerFieldOptions[$key[0]] = $field_info[$key[0]];
  }

  /**
   * {@inheritdoc}
   */
  public function registerOuterFieldOptions($field_info) {
    $this->OuterFieldOptions[] = $field_info;
  }

}
