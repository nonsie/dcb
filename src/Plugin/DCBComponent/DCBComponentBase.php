<?php

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
 * Class DCBComponentBase.
 *
 * @File: Base class for Component plugins.
 *
 * @package Drupal\dcb\Plugin\DCBComponent
 */
abstract class DCBComponentBase extends PluginBase implements DCBComponentInterface, ContainerFactoryPluginInterface {

  public $previewImage;
  public $module;
  public $class;
  public $dir;
  public $properties;
  public $form;
  public $formState;
  public $themes;
  public $namespace;
  public $dcbFieldManager;
  public $fields;
  public $output;
  public $layout;
  public $outerId;
  public $formSettings;
  public $parentTheme;
  public $rebuild;
  public $defaultTheme;
  public $ItemThemes;
  public $InnerFieldOptions;
  public $OuterFieldOptions;
  /**
   * @var ComponentWizardBaseForm
   */
  public $componentForm;

  /**
   * DCBComponentBase constructor.
   *
   * @param array $configuration
   * @param string $pluginId
   * @param mixed $pluginDefinition
   * @param DCBFieldManager $dcbFieldManager
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, DCBFieldManager $dcbFieldManager) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->properties = $this->pluginDefinition['properties'];
    $this->module = $this->properties['module'];
    $this->class = $this->getClass();
    $this->dir = drupal_get_path('module', $this->module);
    $this->themes = $this->getThemes();
    $this->previewImage = $this->getPreviewImageFilePath($this->properties['previewImage']);
    $this->namespace = $this->getNamespace();
    $this->dcbFieldManager = $dcbFieldManager;
    $this->formSettings = $this->pluginDefinition['formSettings'];
    $this->parentTheme = isset($this->pluginDefinition['parentTheme']) ? $this->pluginDefinition['parentTheme'] : [];
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
      $pluginDefinition,
      $container->get('plugin.manager.dcbfield')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getField($id, bool $init = FALSE, FormStateInterface $formState = NULL) {
    $fields = $this->loadFields();
    $field = array_key_exists($id, $fields) ? $fields[$id] : NULL;
    if ($field) {
      if ($init) {
        $newFieldInstance = $this->initField($field);
        $newFieldInstance->init($formState);
        return $newFieldInstance;
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
    return $this->pluginDefinition['defaultTheme'];
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
  public function getOuterForm(ComponentWizardBaseForm $componentForm, array $values) {
    $this->componentForm = $componentForm;
    $this->outerId = $this->componentForm->randId();

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
  public function getRepeatingFields(&$formState = [], $items, $delta) {
    $container_id = $this->componentForm->randId();
    $element['items'] = [
      '#type' => 'details',
      '#title' => t('Item @delta', [
        '@delta' => ($delta + 1),
      ]),
      '#open' => $this->getWidgetDetailsState($formState),
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
  public function formSubmit(FormStateInterface $formState) {

  }

  /**
   * {@inheritdoc}
   */
  public function ajaxCallback($form, FormStateInterface &$formState) {
    $trigger = $formState->getTriggeringElement();
    // This returns a group of fields after an extra field is selected in
    // the UI.
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
  public function preRender($formValues) {
    $this->formState = $formValues;

    if (!empty($formValues['fields'])) {
      $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($formValues['fields']), \RecursiveIteratorIterator::LEAVES_ONLY);
      foreach ($iterator as $key => $value) {
        if ($key === 'handler') {
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
          $field_base_array = NestedArray::getValue($formValues['fields'], $array_item);
          /* @var $field_base_array \Drupal\dcb\Plugin\DCBField\DCBFieldInterface */
          $field_base_array['handler']::preRender($field_base_array['value']);
          $array_item[] = 'value';
          NestedArray::setValue($formValues['fields'], $array_item, $field_base_array['value']);
        }
      }
    }

    $iterator = NULL;
    $key = NULL;
    $value = NULL;
    $array_list = NULL;

    if (!empty($formValues[$formValues['widget']])) {
      $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($formValues[$formValues['widget']]), \RecursiveIteratorIterator::LEAVES_ONLY);
      foreach ($iterator as $key => $value) {
        if ($key === 'handler') {
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
          $field_base_array = NestedArray::getValue($formValues[$formValues['widget']], $array_item);
          /* @var $field_base_array \Drupal\dcb\Plugin\DCBField\DCBFieldInterface */
          $field_base_array['handler']::preRender($field_base_array['value']);
          $array_item[] = 'value';
          NestedArray::setValue($formValues[$formValues['widget']], $array_item, $field_base_array['value']);
        }
      }
    }

    $theme = !empty($this->themes[$formValues['theme']]['handler']) ? $this->themes[$formValues['theme']]['handler'] : NULL;
    if ($theme = $this->loadTheme($theme)) {
      $this->output = $theme->display($formValues);
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
    return new $theme($this->formState, $this);
  }

  /**
   * @param $formState
   * @return bool
   */
  public function getWidgetDetailsState(FormStateInterface $formState) {
    $open = FALSE;
    if (is_object($formState)) {
      $trigger = $formState->getTriggeringElement();
    }
    if (is_object($formState) && isset($trigger['#attributes']['delta'])) {
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
  public function registerInnerFieldOptions($fieldInfo) {
    $key = array_keys($fieldInfo);
    $this->InnerFieldOptions[$key[0]] = $fieldInfo[$key[0]];
  }

  /**
   * {@inheritdoc}
   */
  public function registerOuterFieldOptions($fieldInfo) {
    $this->OuterFieldOptions[] = $fieldInfo;
  }

}
