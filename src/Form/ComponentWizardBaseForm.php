<?php

# @File: not much in here right now, but may be helpful in the future.

namespace Drupal\dynoblock\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ctools\Wizard\FormWizardBase;
use Drupal\dynoblock\Plugin\Dynoblock\DynoblockBase;
use Drupal\dynoblock\Service\DynoblockCore;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ComponentWizardBaseForm extends FormBase {

  public $widget;
  public $sub_widget_ids = array();
  public $method;
  public $form_state;
  public $widget_deltas = array();
  public $core;
  public $wizard;
  public $parameters;

  /**
   * SelectGroup constructor.
   * @param \Drupal\dynoblock\Service\DynoblockCore $core
   */
  public function __construct(DynoblockCore $core) {
    $this->core = $core;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dynoblock.core')
    );
  }


  /**
   * @param \Drupal\ctools\Wizard\FormWizardBase $wizard
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function initwizard(FormWizardBase $wizard, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    $this->wizard = $wizard;
    $this->parameters['step'] = $this->wizard->getStep($cached_values);
  }

  protected function addDefaultFields(&$form, $widget, $nid) {
    $form->form['widget_label'] = array(
      '#weight' => -100,
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => t('<h5><strong>Widget</strong>: <em>@widget</em></h5>', array(
        '@widget' => $widget['name']
      )),
      '#attributes' => array(
        'class' => array('widget-form-widget-type'),
      ),
    );
    // Add node ID if it exists.
    $nid = $nid == 'NA' ? NULL : $nid;
    $form->form['nid'] = array(
      '#type' => 'hidden',
      '#value' => $nid,
    );
  }

  protected function addExtraSettings(&$form, $default_values = array()) {
    $form->form['extra_settings'] = array(
      '#type' => 'details',
      '#title' => t('Widget Settings'),
      '#open' => FALSE,
      '#weight' => 100,
      '#attributes' => array(
        'class' => array('dyno-widget-settings-container'),
      ),
    );
    $form->form['extra_settings'] += _dynoblock_condition_form($default_values);
    $form->form['extra_settings'] += _dynoblock_weight_form($default_values);
    $form->form['extra_settings'] += _dynoblock_add_token_support();
  }

  public function buildWidgetForm($widget, DynoblockBase &$form, FormStateInterface &$form_state) {
    $id = $widget['id'];
    $this->widget = $widget;
    $this->form_state = $form_state;
    $this->widget_deltas[$id] = isset($this->widget_deltas[$id]) && is_numeric($this->widget_deltas[$id]) ? $this->widget_deltas[$id] + 1 : 0;
    $form->form['widget'] = array(
      '#type' => 'hidden',
      '#value' => $id,
    );
    $cardinality = isset($widget['form_settings']['cardinality']) ? $widget['form_settings']['cardinality'] : NULL;
    $variant_support = isset($widget['form_settings']['variant_support']) ? $widget['form_settings']['variant_support'] : NULL;
    if ($cardinality !== NULL && $cardinality !== 0) {
      if (is_object($form_state)) {
        $items = $form_state->getUserInput($id) && !empty($form_state->$id) ? $form_state->$id : $form_state->getUserInput($id);
        $items = $items[$id];
      } else if(!empty($form_state[$id])){
        $items = $form_state[$id];
      }
      $sub_widgets_amounts = count($items);
      if(is_object($form_state) && $form->rebuild) {
        $storage = $form_state->getStorage();
        $sub_widgets_amounts = isset($storage['sub_widgets_amount']) ? $storage['sub_widgets_amount'] : 1;
      }
      $container_id = 'widget-field-groups';
      $form->form[$id] = array(
        '#type' => 'container',
        '#tree' => TRUE,
        '#theme_wrappers' => array('dynoblock_tabledrag'),
        '#attributes' => array(
          'class' => array('widget-field-groups'),
          'id' => 'widget-field-groups',
        ),
      );

      if ($cardinality == -1) {
        $cardinality = empty($items) ? 1 : $sub_widgets_amounts;
      }

      $add_another_name = $id . '[' . $this->widget_deltas[$id] . '][add]';
      $form->form[$id]['add_another'] = self::addAnotherBtn($container_id, $add_another_name);

      if (is_array($items)) {
        $items = array_values($items);
      }

      for ($i = 0; $i < $cardinality; $i++) {
        $sub_widget_id = self::createId($widget, $i . '-sub-widgets');
        $this->sub_widget_ids[$i] = $sub_widget_id;
        $form->form[$id][$i] = array(
          '#type' => 'container',
          '#attributes' => array(
            'class' => array('widget-field-group'),
            'id' => $sub_widget_id,
          ),
        );
        $form->form[$id][$i]['widget'] = $form->widgetForm($form_state, $items, $i);

        if ($cardinality > 1) {
          $name = $id . '[' . $this->widget_deltas[$id] . '][remove][' . $i . ']';
          $form->form[$id][$i]['widget']['items']['remove'] = self::removeItemBtn($id, $i, $container_id, $name);
        }

        if ($variant_support) {
          $form->form[$id][$i]['id'] = array(
            '#type' => 'hidden',
            '#default_value' => (empty($items[$i]['id']) ? self::randId() : $items[$i]['id']),
          );
        }
      }
    }
  }

  public static function randId() {
    return md5(random_bytes(32) . time());
  }

  public function buildParentThemeSettings($widget, &$form, &$form_state) {
    if (!empty($widget['parent_theme']['handler'])) {
      $settings_form = $widget['parent_theme']['handler']->globalSettings($form->form, $form_state);
      if (!empty($settings_form)) {
        $form->form['global_theme_settings'] = array(
          '#type' => 'fieldset',
          '#weight' => 101,
          '#tree' => TRUE,
          '#title' => t('Global Theme Settings'),
          '#collapsed' => TRUE,
          '#collapsible' => TRUE,
        );
        $form->form['global_theme_settings'] += $settings_form;
        $form->form['global_theme_settings']['handler'] = array(
          '#type' => 'hidden',
          '#value' => get_class($widget['parent_theme']['handler']),
        );
      }
    }
  }

  public function buildThemeSelection($widget, &$form, FormStateInterface &$form_state) {
    if (!empty($form->themes)) {
      $number_of_themes = count($form->themes);
      $container_id = self::createId($widget, 'theme_overview');
      $preview_container_id = self::createId($widget, 'preview');
      $form->form['theme_overview'] = array(
        '#type' => 'container',
        '#weight' => -99,
        '#attributes' => array(
          'class' => array('dyno-theme-overview'),
          'id' => $container_id,
        ),
      );
      $form->form['theme_overview']['preview'] = array(
        '#weight' => 0,
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => '',
        '#attributes' => array(
          'class' => array('dyno-theme-preview'),
          'id' => $preview_container_id,
        ),
      );
      if(is_object($form_state)) {
        $default = !empty($form_state->getUserInput('theme')) ? $form_state->getUserInput('theme') : NULL;
        $default = !empty($form_state->getUserInput('theme_overview')['theme']) ? $form_state->getUserInput('theme_overview')['theme'] : $default;
        $default = $form_state->theme ? $form_state->theme : $default;
      } else {
        $default = !empty($form_state['theme']) ? $form_state['theme'] : NULL;
      }
      // # TODO: If they never want to show the default theme preview
      // # need to add hidden value to $this->form with the default theme.
      // Theme selection select list.
      $theme_options = array();
      foreach($form->themes as $template => $properties){
        $theme_options[$template] = $properties['label'];
      }
      $form->form['theme_overview']['theme'] = array(
        '#type' => 'select',
        '#weight' => -100,
        '#title' => t('Layout Theme'),
        '#description' => t('Select A Widget Theme.'),
        '#options' => $theme_options,
        '#default_value' => $default,
        '#attributes' => array(
          'target' => $container_id,
        ),
        '#ajax' => array(
          'url' => Url::fromRoute('dynoblock.admin.wizard.ajax.step', $this->parameters),
          'options' => ['query' => \Drupal::request()->query->all() + [FormBuilderInterface::AJAX_FORM_REQUEST => TRUE]],
          'callback' => [$this, 'fieldAjaxCallback'],
          'wrapper' => $container_id,
          'method' => 'replace',
          'type' => 'widget_theme',
        ),
      );
      $theme_selected = empty($default) && !empty($form->default_theme) ? $form->default_theme : $default;
      if ($theme_selected && !empty($form->themes[$theme_selected])) {
        $theme_selected = $form->themes[$theme_selected]['handler'];
        $theme = $form->loadTheme($theme_selected);
        // Dont show preview unless they select a different one.
        // This happens because they already know what the default one looks like.
        if ($number_of_themes > 1 && is_object($form_state) && !empty($form_state)) {
          $form->form['theme_overview']['preview']['#value'] = $theme->preview();
        }
        // Hide theme selection if there is only one option.
        // Create a hidden field containing the default theme handler.
        if ($number_of_themes <= 1) {
          $form->form['theme_overview']['theme'] = array(
            '#type' => 'hidden',
            '#value' => key($form->themes),
          );
        }
        $theme_form = $theme->form($form->form, $form_state);
        if (!empty($theme_form) && is_array($theme_form)) {
          $form->form['theme_overview']['theme_settings'] = array(
            '#type' => 'fieldset',
            '#title' => t('Theme Settings'),
            '#weight' => 1,
            '#tree' => TRUE,
            '#collapsed' => TRUE,
            '#collapsible' => TRUE,
            '#attributes' => array(
              'class' => array('dyno-theme-settings'),
            ),
          );
          $form->form['theme_overview']['theme_settings'] += $theme_form;
        }
      }
    }
  }

  public function getPreview($widget) {
    if (!empty($widget->preview_image)) {
      $image_path = $widget->preview_image;
      return array(
        '#type' => 'markup',
        '#markup' => '<img height="280px" src="' . $image_path . '"/>',
      );
    }
  }

  public function themeOptions($plugin, &$item, $delta, $values, $container_id, $themes) {
    $number_of_themes = count($themes['themes']);
    $item['preview'] = array(
      '#weight' => -99,
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => '',
      '#attributes' => array(
        'class' => array('dyno-sub-item', 'dyno-sub-theme-preview'),
      ),
    );
    $theme_selected = !empty($values['theme']) ? $values['theme'] : $themes['default'];
    $item['theme'] = array(
      '#type' => 'select',
      '#weight' => -100,
      '#title' => t('Item Theme'),
      '#description' => t('Select A Widget Sub Theme.'),
      '#options' => $themes['themes'],
      '#default_value' => $theme_selected,
      '#ajax' => array(
        'url' => Url::fromRoute('dynoblock.admin.wizard.ajax.step', $this->parameters),
        'options' => ['query' => \Drupal::request()->query->all() + [FormBuilderInterface::AJAX_FORM_REQUEST => TRUE]],
        'callback' => [$this, 'fieldAjaxCallback'],
        'wrapper' => $container_id,
        'method' => 'replace',
        'type' => 'sub_item_theme',
      ),
      '#attributes' => array(
        'delta' => $delta,
        'data-drupal-target' => $container_id,
      ),
    );

    if ($theme_selected && $theme_selected != 'default') {
      $theme = $plugin ->loadTheme($theme_selected);
      //if ($number_of_themes > 1 && (self::$method != 'edit' || !is_object(self::$form_state) && !empty(self::$form_state['dyno_system']['themes_selected'][$container_id]))) {
      $item['preview']['#value'] = $theme->preview();
      //}
      if ($number_of_themes <= 1) {
        $item['theme'] = array(
          '#type' => 'hidden',
          '#value' => $themes['default'],
        );
      }
      $theme->form($item);
    }
    if(!is_object($this->form_state)) {
      if (empty($this->form_state['dyno_system']['themes_selected'][$container_id])) {
        $this->form_state['dyno_system']['themes_selected'][$container_id] = $theme_selected;
      }
    }
  }

  public function fieldOptions($plugin, &$form, $values, $wrapper, $fields, $delta = 0) {
    $fields_selected = !empty($values['field_options']) ? $values['field_options'] : array();
    $field_options = array();
    foreach ($fields as $key => $field) {
      $field_options[$field['plugin'] . '|' . $field['field_name'] . '|' . $key] = $field['label'];
    }
    $form['field_options'] = array(
      '#type' => 'checkboxes',
      '#weight' => -99,
      '#title' => t('Extra Fields'),
      '#description' => t('Add Extra Fields.'),
      '#options' => $field_options,
      '#multiple' => TRUE,
      //'#value' => $fields_selected,
      '#default_value' => $fields_selected,
      '#ajax' => array(
        'url' => Url::fromRoute('dynoblock.admin.wizard.ajax.step', $this->parameters),
        'options' => ['query' => \Drupal::request()->query->all() + [FormBuilderInterface::AJAX_FORM_REQUEST => TRUE]],
        'callback' => [$this, 'extraFieldCallback'],
        'wrapper' => $wrapper,
        'method' => 'replaceWith',
        'event' => 'change',
        'delta' => $delta,
      ),
      '#attributes' => array(
        'delta' => $delta,
        'target' => $wrapper,
        'class' => array('dynoblock-extra-fields'),
      ),
    );
    if (!empty($fields_selected)) {
      foreach ($fields_selected as $class) {
        if (!empty($class)) {
          list($field_plugin, $field_name, $delta) = explode('|', $class);
          $field = $plugin->getField($field_plugin, TRUE, $this->form_state);
          $form[$field_name] = $field->form(!empty($fields[$delta]['properties']) ? $fields[$delta]['properties'] : []);
        }
      }
    }
  }


  public function addAnotherBtn($id, $name) {
    return array(
      '#type' => 'submit',
      '#submit' => [[$this, 'cardinalitySubmit']],
      '#value' => t('Add Another'),
      '#weight' => 100,
      '#name' => $name,
      '#button_type' => 'primary',
      '#ajax' => array(
        'url' => Url::fromRoute('dynoblock.admin.wizard.ajax.step', $this->parameters),
        'options' => ['query' => \Drupal::request()->query->all() + [FormBuilderInterface::AJAX_FORM_REQUEST => TRUE]],
        'wrapper' => $id,
        'callback' => [$this, 'cardinalityCallback'],
        'method' => 'replaceWith',
        'effect' => 'fade',
        'type' => 'add',
      ),
      '#attributes' => array(
        '#type' => 'add',
        'data-drupal-target' => $id,
      ),
    );
  }

  public function removeItemBtn($widget, $delta, $ajax_target, $name) {
    return array(
      '#type' => 'submit',
      '#submit' => [[$this, 'cardinalitySubmit']],
      '#value' => t('Remove'),
      '#weight' => 100,
      '#name' => $name,
      '#attributes' => array(
        '#type' => 'remove',
        'class' => array('btn-danger'),
        'target' => $ajax_target,
      ),
      '#ajax' => array(
        'url' => Url::fromRoute('dynoblock.admin.wizard.ajax.step', $this->parameters),
        'options' => ['query' => \Drupal::request()->query->all() + [FormBuilderInterface::AJAX_FORM_REQUEST => TRUE]],
        'wrapper' => $ajax_target,
        'callback' => [$this, 'cardinalityCallback'],
        'method' => 'replaceWith',
        'effect' => 'fade',
        'type' => 'remove',
        'delta' => $delta,
        'widget' => $widget,
      ),
    );
  }

  public function createId($widget, $type) {
    $id = !empty($this->widget_deltas[$widget['id']]) ? $this->widget_deltas[$widget['id']] : 0;
    return $widget['id'] . '-' . $id . '-' . $type;
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return mixed
   */
  public function cardinalityCallback(array &$form, FormStateInterface &$form_state) {
    $trigger = $form_state->getTriggeringElement();
    switch ($trigger['#ajax']['type']) {
      case 'add':
      case 'remove':
        return $form[$form_state->getValue('widget')];
        break;
    }
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function cardinalitySubmit(array &$form, FormStateInterface &$form_state) {
    $form_state->setRebuild(TRUE);
    $trigger = $form_state->getTriggeringElement();
    $type = $trigger['#attributes']['#type'];
    $storage = &$form_state->getStorage();
    $storage['sub_widgets_amount'] = isset($storage['sub_widgets_amount']) ? $storage['sub_widgets_amount'] : 1;
    switch ($type) {
      case 'remove':
        $input = &$form_state->getUserInput();
        $plugin_id = $input['widget'];
        $plugin_values = &$input[$plugin_id];
        $delta = $trigger['#ajax']['delta'];
        if (isset($plugin_values[$delta])) {
          unset($plugin_values[$delta]);
          $plugin_values = array_values($plugin_values);
          $form_state->setUserInput($input);
        }
        $storage['sub_widgets_amount']--;
        break;
      case 'add':
        $storage['sub_widgets_amount']++;
        break;
    }
    $form_state->setStorage($storage);
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return mixed
   */
  public function fieldAjaxCallback(array $form = array(), FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $type = $trigger['#ajax']['type'];
    switch ($type) {
      case 'sub_item_theme':
        array_pop($trigger['#array_parents']);
        array_pop($trigger['#array_parents']);
        array_pop($trigger['#array_parents']);
        return NestedArray::getValue($form, $trigger['#array_parents']);
        break;
      case 'widget_theme':
        return $form['theme_overview'];
        break;
    }
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function fieldAjaxSubmit(array $form = array(), FormStateInterface $form_state) {

  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function extraFieldCallback(array $form = array(), FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    array_pop($trigger['#array_parents']);
    array_pop($trigger['#array_parents']);
    array_pop($trigger['#array_parents']);
    array_pop($trigger['#array_parents']);
    return NestedArray::getValue($form, $trigger['#array_parents']);
  }





  /* public function editForm($rid, $bid, $nid) {
   self::$method = 'edit';
   $core = \Drupal::service('dynoblock.core');
   if ($bid) {
     $block = $core->db->getBlock($rid, $bid);
     if ($block) {
       $widget = !empty($block['widget']) ? $block['widget'] : $block['layout_id'];
       $plugin = $core->initPlugin($widget);
       $widget = $core->getWidget($widget);
       if ($plugin && $widget) {
         $plugin->init()->build($block);
         self::addDefaultFields($plugin, $widget, $nid);
         self::addExtraSettings($plugin, $block);
         self::buildWidgetForm($widget, $plugin, $block);
         self::buildThemeSelection($widget, $plugin, $block);
         self::buildParentThemeSettings($widget, $plugin, $block);
         $form = \Drupal::formBuilder()->getForm('Drupal\dynoblock\Form\DynoblockForm', $plugin->form);
         $commands = $core->getAjaxCommands($form);
         return compact('form', 'commands');
       }
     }
   }
   return array(
     '#type' => 'markup',
     '#markup' => '',
   );
 }*/


}
