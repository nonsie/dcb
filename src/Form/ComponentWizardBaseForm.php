<?php

namespace Drupal\dcb\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ctools\Wizard\FormWizardBase;
use Drupal\dcb\Base\Component\DCBComponentBase;
use Drupal\dcb\Service\DCBCore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @File: Base form that all steps of the wizard extend. Contains functions
 * necessary for creating the DCB admin forms.
 */

/**
 * Class ComponentWizardBaseForm.
 *
 * @package Drupal\dcb\Form
 */
abstract class ComponentWizardBaseForm extends FormBase {

  /**
   * @var array
   */
  public $subWidgetIds = [];

  /**
   * @var
   */
  public $method;

  /**
   * @var
   */
  public $formState;

  /**
   * @var array
   */
  public $widgetDeltas = [];

  /**
   * @var \Drupal\dcb\Service\DCBCore
   */
  public $core;

  /**
   * @var
   */
  public $wizard;

  /**
   * @var
   */
  public $parameters;

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  public $request;

  /**
   * @var
   */
  public $formSettings;

  /**
   * @var \Drupal\dcb\Plugin\DCBComponent\DCBComponentBase
   */
  public $componentInstance;

  /**
   * SelectGroup constructor.
   *
   * @param \Drupal\dcb\Service\DCBCore $core
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   */
  public function __construct(DCBCore $core, RequestStack $request) {
    $this->core = $core;
    $this->request = $request;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dcb.core'),
      $container->get('request_stack')
    );
  }

  /**
   * @param \Drupal\ctools\Wizard\FormWizardBase $wizard
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public function initwizard(FormWizardBase $wizard, FormStateInterface $formState) {
    $cached_values = $formState->getTemporaryValue('wizard');
    $this->wizard = $wizard;
    $this->parameters['step'] = $this->wizard->getStep($cached_values);
    $this->formState = $formState;
  }

  /**
   * @param \Drupal\dcb\Base\Component\DCBComponentBase $componentInstance
   */
  public function setComponentInstance(DCBComponentBase $componentInstance) {
    $this->componentInstance = $componentInstance;
  }

  /**
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  protected function setArgsFromUri(FormStateInterface $formState) {
    $args = UrlHelper::parse($this->request->getCurrentRequest()
      ->getUri())['query'];
    $expected_args = ['rid', 'bid', 'etype', 'eid'];
    foreach ($expected_args as $arg) {
      if (isset($args[$arg])) {
        $formState->set($arg, $args[$arg]);
      }
    }
  }

  /**
   * @param $cached_values
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  protected function setArgsFromCache($cached_values, FormStateInterface $formState) {
    $expected_args = ['rid', 'bid', 'etype', 'eid'];
    foreach ($expected_args as $arg) {
      if (isset($cached_values[$arg])) {
        $formState->set($arg, $cached_values[$arg]);
      }
    }
  }

  /**
   * @param $eid
   */
  protected function addDefaultFields($eid) {
    $this->componentInstance->form['widget_label'] = [
      '#weight' => -100,
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => t('<h5><strong>Component</strong>: <em>@component</em></h5>', [
        '@component' => $this->componentInstance->getName(),
      ]),
      '#attributes' => [
        'class' => ['widget-form-widget-type'],
      ],
    ];
    // Add node ID if it exists.
    $eid = $eid == 'NA' ? NULL : $eid;
    $this->componentInstance->form['nid'] = [
      '#type' => 'hidden',
      '#value' => $eid,
    ];
  }

  /**
   * @param array $default_values
   */
  protected function addExtraSettings(array $default_values = []) {
    $this->componentInstance->form['extra_settings'] = [
      '#type' => 'details',
      '#title' => t('Component Settings'),
      '#open' => FALSE,
      '#weight' => 100,
      '#attributes' => [
        'class' => ['dyno-widget-settings-container'],
      ],
    ];
    $this->componentInstance->form['extra_settings'] += $this->dcbConditionForm($default_values);
    $this->componentInstance->form['extra_settings'] += $this->dcbWeightForm($default_values);

    // TODO: token tree does not load since theme function is not available.
    // $form->form['extra_settings'] += _dynoblock_add_token_support();
  }

  /**
   * @param array $values
   *
   * @return mixed
   */
  public static function dcbConditionForm(array $values = []) {
    $condition['conditions'] = [
      '#type' => 'details',
      '#weight' => 98,
      '#attributes' => [
        'class' => ['dyno-condition'],
      ],
    ];
    $condition['conditions']['label'] = [
      '#type' => 'markup',
      '#markup' => '<label>Conditions</label>',
    ];
    $condition['conditions']['description'] = [
      '#type' => 'markup',
      '#markup' => '<p>Enter a conditional that needs to return true for a this block to be displayed. If no conditionals are added, this component will display by default.</p>',
    ];
    $condition['conditions']['condition_token'] = [
      '#type' => 'textfield',
      '#title' => t('Token'),
      '#description' => t('Token for conditional.'),
      '#size' => 40,
      '#maxlength' => 255,
      '#name' => 'condition_token',
      '#value' => isset($values['condition_token']) ? $values['condition_token'] : '',
    ];

    $condition['conditions']['operators'] = [
      '#type' => 'select',
      '#title' => t('Operator'),
      '#description' => t('The description appears usually below the item.'),
      '#options' => [
        '==' => '==',
        '===' => '===',
        '!=' => '!=',
        '!==' => '!==',
        '<' => '<',
        '>' => '>',
        '<=' => '<=',
        '>=' => '>=',
      ],
      '#default_value' => -1,
      '#name' => 'condition_operator',
      '#value' => !empty($values['condition_operator']) ? $values['condition_operator'] : '',
    ];

    $condition['conditions']['value'] = [
      '#type' => 'textfield',
      '#title' => t('Token value'),
      '#size' => 20,
      '#maxlength' => 255,
      '#name' => 'condition_value',
      '#value' => isset($values['condition_value']) ? $values['condition_value'] : '',
    ];
    return $condition;
  }

  /**
   * @param array $values
   *
   * @return mixed
   */
  public static function dcbWeightForm(array $values = []) {
    $form['weight'] = [
      '#type' => 'container',
      '#weight' => 97,
      '#attributes' => [
        'class' => ['dyno-weight'],
      ],
    ];
    $form['weight']['weight'] = [
      '#type' => 'textfield',
      '#title' => t('Weight'),
      '#size' => 40,
      '#maxlength' => 255,
      '#value' => isset($values['weight']) ? $values['weight'] : 0,
    ];
    return $form;
  }

  /**
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public function buildWidgetForm(FormStateInterface &$formState) {
    $id = $this->componentInstance->getId();
    $this->widgetDeltas[$id] = isset($this->widgetDeltas[$id]) && is_numeric($this->widgetDeltas[$id]) ? $this->widgetDeltas[$id] + 1 : 0;
    $this->componentInstance->form['widget'] = [
      '#type' => 'hidden',
      '#value' => $id,
    ];
    $cardinality = isset($this->componentInstance->formSettings['cardinality']) ? $this->componentInstance->formSettings['cardinality'] : NULL;
    $variant_support = isset($this->componentInstance->formSettings['variant_support']) ? $this->componentInstance->formSettings['variant_support'] : NULL;
    if (!empty($cardinality)) {
      if (!empty($formState->getValue($id))) {
        $items = $formState->getValue($id);
      }
      else {
        $items = [];
      }
      $sub_widgets_amounts = count($items);
      if (is_object($formState) && isset($this->componentInstance->rebuild) && $this->componentInstance->rebuild == TRUE) {
        $storage = $formState->getStorage();
        $sub_widgets_amounts = isset($storage['sub_widgets_amount']) ? $storage['sub_widgets_amount'] : 1;
      }
      $container_id = 'widget-field-groups';
      $this->componentInstance->form[$id] = [
        '#type' => 'container',
        '#tree' => TRUE,
        '#theme_wrappers' => ['dcb_tabledrag'],
        '#attributes' => [
          'class' => ['widget-field-groups'],
          'id' => 'widget-field-groups',
        ],
      ];

      if ($cardinality == -1) {
        $cardinality = empty($items) ? 1 : $sub_widgets_amounts;
      }

      $add_another_name = $id . '[' . $this->widgetDeltas[$id] . '][add]';
      $this->componentInstance->form['add_another'] = self::addAnotherBtn($container_id, $add_another_name);

      if (is_array($items)) {
        $items = array_values($items);
      }

      for ($i = 0; $i < $cardinality; $i++) {
        $sub_widget_id = self::createId($id, $i . '-sub-widgets');
        $this->subWidgetIds[$i] = $sub_widget_id;
        $this->componentInstance->form[$id][$i] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['widget-field-group'],
            'id' => $sub_widget_id,
          ],
        ];

        $this->componentInstance->form[$id][$i]['widget'] = $this->componentInstance->getRepeatingFields($formState, $items, $i);

        if ($cardinality > 1) {
          $name = $id . '[' . $this->widgetDeltas[$id] . '][remove][' . $i . ']';
          $this->componentInstance->form[$id][$i]['widget']['items']['remove'] = self::removeItemBtn($id, $i, $container_id, $name);
        }

        if ($variant_support) {
          $this->componentInstance->form[$id][$i]['id'] = [
            '#type' => 'hidden',
            '#default_value' => (empty($items[$i]['id']) ? self::randId() : $items[$i]['id']),
          ];
        }
      }
      $this->fieldOptions('repeating');
      $this->themeOptions();
    }
    $this->fieldOptions('outer');
  }

  /**
   *
   */
  public function themeOptions() {
    if (!empty($this->componentInstance->ItemThemes)) {
      foreach ($this->componentInstance->ItemThemes as $delta => $themes) {
        $number_of_themes = count($themes['themes']);
        $innerform = &$this->componentInstance->form[$this->componentInstance->getId()][$delta]['widget']['items'];
        $values = $this->formState->getValue([
          $this->componentInstance->getId(),
          $delta,
          'widget',
          'items',
        ]);

        $innerform['preview'] = [
          '#weight' => -99,
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => '',
          '#attributes' => [
            'class' => ['dyno-sub-item', 'dyno-sub-theme-preview'],
          ],
        ];

        $theme_selected = !empty($values['theme']) ? $values['theme'] : $themes['default'];

        $innerform['theme'] = [
          '#type' => 'select',
          '#weight' => -100,
          '#title' => t('Item Theme'),
          '#description' => t('Select A Widget Sub Theme.'),
          '#options' => $themes['themes'],
          '#default_value' => $theme_selected,
          '#ajax' => [
            'url' => Url::fromRoute('dcb.admin.wizard.ajax.step', $this->parameters),
            'options' => ['query' => \Drupal::request()->query->all() + [FormBuilderInterface::AJAX_FORM_REQUEST => TRUE]],
            'callback' => [$this, 'fieldAjaxCallback'],
            'wrapper' => $innerform['#attributes']['id'],
            'method' => 'replace',
            'type' => 'sub_item_theme',
          ],
          '#attributes' => [
            'delta' => $delta,
            'data-drupal-target' => $innerform['#attributes']['id'],
          ],
        ];

        if ($theme_selected && $theme_selected != 'default') {
          $theme = $this->componentInstance->loadTheme($theme_selected);
          $innerform['preview']['#value'] = $theme->preview();

          if ($number_of_themes <= 1) {
            $innerform['theme'] = [
              '#type' => 'hidden',
              '#value' => $themes['default'],
            ];
          }
          $theme->form($innerform);
        }
      }
    }
  }

  /**
   * @param $location
   */
  public function fieldOptions($location) {

    switch ($location) {
      case 'outer':
        $registered_fields = $this->componentInstance->OuterFieldOptions;
        break;

      case 'repeating':
        $registered_fields = $this->componentInstance->InnerFieldOptions;
        break;
    }

    // Check that Inner Field options have been registered.
    if (!empty($registered_fields)) {
      // Loop Through the fields by item delta.
      foreach ($registered_fields as $delta => $field_def) {

        switch ($location) {
          case 'outer':
            $innerform = &$this->componentInstance->form['fields'];
            $values = $this->formState->getValue(['fields']);
            break;

          case 'repeating':
            $innerform = &$this->componentInstance->form[$this->componentInstance->getId()][$delta]['widget']['items'];
            $values = $this->formState->getValue([
              $this->componentInstance->getId(),
              $delta,
              'widget',
              'items',
            ]);
            break;
        }

        $fields_selected = !empty($values['field_options']) ? $values['field_options'] : [];
        $field_options = [];
        // For each of the deltas, loop through the fields since there could be
        // multiple.
        foreach ($field_def as $key => $field) {
          $field_options[$field['plugin'] . '|' . $field['field_name'] . '|' . $key] = $field['label'];
        }
        // Build out the selection form.
        $innerform['field_options'] = [
          '#type' => 'checkboxes',
          '#weight' => -98,
          '#title' => t('Extra Fields'),
          '#description' => t('Add Extra Fields.'),
          '#options' => $field_options,
          '#multiple' => TRUE,
          '#default_value' => $fields_selected,
          '#ajax' => [
            'url' => Url::fromRoute('dcb.admin.wizard.ajax.step', $this->parameters),
            'options' => ['query' => \Drupal::request()->query->all() + [FormBuilderInterface::AJAX_FORM_REQUEST => TRUE]],
            'callback' => [$this, 'extraFieldCallback'],
            'wrapper' => $innerform['#attributes']['id'],
            'method' => 'replaceWith',
            'event' => 'change',
            'delta' => $delta,
            'plugin' => $this->componentInstance->getId(),
            'type' => $location,
          ],
          '#attributes' => [
            'delta' => $delta,
            'target' => $innerform['#attributes']['id'],
            'class' => ['dynoblock-extra-fields'],
          ],
        ];

        if (!empty($fields_selected)) {
          foreach ($fields_selected as $class) {
            if (!empty($class)) {
              list($field_plugin, $field_name, $delta) = explode('|', $class);
              $field = $this->componentInstance->getField($field_plugin, TRUE, $this->formState);
              $innerform[$field_name] = $field->form(!empty($field_def[$delta]['properties']) ? $field_def[$delta]['properties'] : []);
            }
          }
        }
      }
    }
  }

  /**
   * @return string
   */
  public static function randId() {
    return md5(random_bytes(32) . time());
  }

  /**
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public function buildParentThemeSettings(FormStateInterface &$formState) {
    if (isset($this->componentInstance->parentTheme['handler']) && !empty($this->componentInstance->parentTheme['handler'])) {
      $settings_form = $this->componentInstance->parentTheme['handler']->globalSettings($this->componentInstance->form, $formState->getValues());
      if (!empty($settings_form)) {
        $this->componentInstance->form['global_theme_settings'] = [
          '#type' => 'fieldset',
          '#weight' => 101,
          '#tree' => TRUE,
          '#title' => t('Global Theme Settings'),
          '#collapsed' => TRUE,
          '#collapsible' => TRUE,
        ];
        $this->componentInstance->form['global_theme_settings'] += $settings_form;
        $this->componentInstance->form['global_theme_settings']['handler'] = [
          '#type' => 'hidden',
          '#value' => get_class($this->componentInstance->parentTheme['handler']),
        ];
      }
    }
  }

  /**
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public function buildThemeSelection(FormStateInterface &$formState) {
    if (!empty($this->componentInstance->themes)) {
      $number_of_themes = count($this->componentInstance->themes);
      $container_id = self::createId($this->componentInstance->getId(), 'theme_overview');
      $preview_container_id = self::createId($this->componentInstance->getId(), 'preview');
      $this->componentInstance->form['theme_overview'] = [
        '#type' => 'container',
        '#weight' => -99,
        '#attributes' => [
          'class' => ['dyno-theme-overview'],
          'id' => $container_id,
        ],
      ];
      $this->componentInstance->form['theme_overview']['preview'] = [
        '#weight' => 0,
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => '',
        '#attributes' => [
          'class' => ['dyno-theme-preview'],
          'id' => $preview_container_id,
        ],
      ];
      if (!empty($formState->getValue('theme'))) {
        $default = $formState->getValue('theme');
      }
      else {
        $default = NULL;
      }
      // # TODO: If they never want to show the default theme preview
      // # need to add hidden value to $this->form with the default theme.
      // Theme selection select list.
      $theme_options = [];
      foreach ($this->componentInstance->themes as $template => $properties) {
        $theme_options[$template] = $properties['label'];
      }
      $this->componentInstance->form['theme_overview']['theme'] = [
        '#type' => 'select',
        '#weight' => -100,
        '#title' => t('Layout Theme'),
        '#description' => t('Select A Widget Theme.'),
        '#options' => $theme_options,
        '#default_value' => $default,
        '#attributes' => [
          'target' => $container_id,
        ],
        '#ajax' => [
          'url' => Url::fromRoute('dcb.admin.wizard.ajax.step', $this->parameters),
          'options' => ['query' => \Drupal::request()->query->all() + [FormBuilderInterface::AJAX_FORM_REQUEST => TRUE]],
          'callback' => [$this, 'fieldAjaxCallback'],
          'wrapper' => $container_id,
          'method' => 'replace',
          'type' => 'widget_theme',
        ],
      ];
      $theme_selected = empty($default) && !empty($this->componentInstance->defaultTheme) ? $this->componentInstance->defaultTheme : $default;
      if ($theme_selected && !empty($this->componentInstance->themes[$theme_selected])) {
        $theme_selected = $this->componentInstance->themes[$theme_selected]['handler'];
        $theme = $this->componentInstance->loadTheme($theme_selected);
        // Don't show preview unless they select a different one.
        // This happens because they already know what the default one looks
        // like.
        if ($number_of_themes > 1 && is_object($formState) && !empty($formState)) {
          $this->componentInstance->form['theme_overview']['preview']['#value'] = $theme->preview();
        }
        // Hide theme selection if there is only one option.
        // Create a hidden field containing the default theme handler.
        if ($number_of_themes <= 1) {
          $this->componentInstance->form['theme_overview']['theme'] = [
            '#type' => 'hidden',
            '#value' => key($this->componentInstance->themes),
          ];
        }
        $theme_form = $theme->form($this->componentInstance->form, $formState);
        if (!empty($theme_form) && is_array($theme_form)) {
          $this->componentInstance->form['theme_overview']['theme_settings'] = [
            '#type' => 'fieldset',
            '#title' => t('Theme Settings'),
            '#weight' => 1,
            '#tree' => TRUE,
            '#collapsed' => TRUE,
            '#collapsible' => TRUE,
            '#attributes' => [
              'class' => ['dyno-theme-settings'],
            ],
          ];
          $this->componentInstance->form['theme_overview']['theme_settings'] += $theme_form;
        }
      }
    }
  }

  /**
   * @param $widget
   *
   * @return array
   */
  public static function getPreview($widget) {
    if (!empty($widget->previewImage)) {
      $image_path = $widget->previewImage;
      return [
        '#type' => 'markup',
        '#markup' => '<img height="280px" src="' . $image_path . '"/>',
      ];
    }
    else {
      return [
        '#type' => 'markup',
        '#markup' => 'No Preview',
      ];
    }
  }

  /**
   * @param $ajax_target
   * @param $name
   *
   * @return array
   */
  public function addAnotherBtn($ajax_target, $name) {
    return [
      'outer' => [
        '#type' => 'container',
        '#attributes' => [
          'style' => [
            'padding: 0px 0px 15px 15px',
          ],
        ],
        'submit' => [
          '#type' => 'submit',
          '#submit' => [[$this, 'cardinalitySubmit']],
          '#value' => t('Add Another'),
          '#weight' => 100,
          '#name' => $name,
          '#button_type' => 'primary',
          '#ajax' => [
            'url' => Url::fromRoute('dcb.admin.wizard.ajax.step', $this->parameters),
            'options' => ['query' => \Drupal::request()->query->all() + [FormBuilderInterface::AJAX_FORM_REQUEST => TRUE]],
            'wrapper' => $ajax_target,
            'callback' => [$this, 'cardinalityCallback'],
            'method' => 'replaceWith',
            'effect' => 'fade',
            'type' => 'add',
          ],
          '#attributes' => [
            '#type' => 'add',
            'data-drupal-target' => $ajax_target,
          ],
        ],
      ],
    ];
  }

  /**
   * @param $componentName
   * @param $delta
   * @param $ajax_target
   * @param $name
   *
   * @return array
   */
  public function removeItemBtn($componentName, $delta, $ajax_target, $name) {
    return [
      '#type' => 'submit',
      '#submit' => [[$this, 'cardinalitySubmit']],
      '#value' => t('Remove'),
      '#weight' => 100,
      '#name' => $name,
      '#attributes' => [
        '#type' => 'remove',
        'class' => ['btn-danger'],
        'target' => $ajax_target,
      ],
      '#ajax' => [
        'url' => Url::fromRoute('dcb.admin.wizard.ajax.step', $this->parameters),
        'options' => ['query' => \Drupal::request()->query->all() + [FormBuilderInterface::AJAX_FORM_REQUEST => TRUE]],
        'wrapper' => $ajax_target,
        'callback' => [$this, 'cardinalityCallback'],
        'method' => 'replaceWith',
        'effect' => 'fade',
        'type' => 'remove',
        'delta' => $delta,
        'widget' => $componentName,
      ],
    ];
  }

  /**
   * @param $componentInstanceType
   * @param $type
   *
   * @return string
   */
  public function createId($componentInstanceType, $type) {
    $id = !empty($this->widgetDeltas[$componentInstanceType]) ? $this->widgetDeltas[$componentInstanceType] : 0;
    return $componentInstanceType . '-' . $id . '-' . $type;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *
   * @return array|mixed
   */
  public function cardinalityCallback(array &$form, FormStateInterface &$formState) {
    $trigger = $formState->getTriggeringElement();
    switch ($trigger['#ajax']['type']) {
      case 'add':
      case 'remove':
        return $form[$formState->getValue('widget')];

      default:
        return [];
    }
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public function cardinalitySubmit(array &$form, FormStateInterface &$formState) {
    $formState->setRebuild(TRUE);
    $trigger = $formState->getTriggeringElement();
    $type = $trigger['#attributes']['#type'];
    $storage = $formState->getStorage();
    $storage['sub_widgets_amount'] = isset($storage['sub_widgets_amount']) ? $storage['sub_widgets_amount'] : 1;

    switch ($type) {
      case 'remove':
        $input = &$formState->getUserInput();
        $pluginId = $input['widget'];
        $plugin_values = &$input[$pluginId];
        $delta = $trigger['#ajax']['delta'];
        if (isset($plugin_values[$delta])) {
          unset($plugin_values[$delta]);
          $plugin_values = array_values($plugin_values);
          $formState->setUserInput($input);
        }
        $storage['sub_widgets_amount']--;
        break;

      case 'add':
        $storage['sub_widgets_amount']++;
        break;
    }
    $formState->setStorage($storage);
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *
   * @return array|mixed
   */
  public function fieldAjaxCallback(array $form = [], FormStateInterface $formState) {
    $trigger = $formState->getTriggeringElement();
    $type = $trigger['#ajax']['type'];

    switch ($type) {
      case 'sub_item_theme':
        array_pop($trigger['#array_parents']);
        array_pop($trigger['#array_parents']);
        array_pop($trigger['#array_parents']);
        return NestedArray::getValue($form, $trigger['#array_parents']);

      case 'widget_theme':
        return $form['theme_overview'];

      default:
        return [];
    }
  }

  /**
   * @param \Drupal\Core\Form\FormStateInterface $formState
   * @param array $form
   */
  public function fieldajaxSubmit(FormStateInterface $formState, array $form = []) {

  }

  /**
   * @param \Drupal\Core\Form\FormStateInterface $formState
   * @param array $form
   *
   * @return mixed
   */
  public function extraFieldCallback(array $form = [], FormStateInterface $formState) {
    $trigger = $formState->getTriggeringElement();
    if ($trigger['#ajax']['type'] == 'outer') {
      // If this is an outer triggering the extra field, return the outer
      // container.
      return $form['fields'];
    }
    elseif ($trigger['#ajax']['type'] == 'repeating') {
      // If this is a repeating element, return the container for the correct
      // delta.
      return $form[$trigger['#ajax']['plugin']][$trigger['#ajax']['delta']];
    }
  }

}
