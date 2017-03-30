<?php

namespace Drupal\dynoblock;

class DynoBlockForms {

  const coreAjaxCallback = 'dynoblock_core_form_ajax_callback';
  const coreAjaxSubmit = 'dynoblock_core_form_ajax_submit';
  const fieldAjaxCallback = 'dynoblock_form_ajax_callback';
  const fieldAjaxSubmit = 'dynoblock_form_ajax_submit';
  const fieldWidgetAjaxCallback = 'dynoblock_field_widget_ajax_callback';
  const widgetSettingsCallback = 'dynoblock_widget_settings_callback';

  public static $widget;
  public static $sub_widget_ids = array();
  public static $method;
  public static $form_state;
  public static $widget_deltas = array();


  public static function generateForm($type, $rid, $nid) {
    self::$method = 'new';
    $core = \Drupal::service('dynoblock.core');
    $plugin = $core->initPlugin($type);
    $widget = $core->getWidget($type);
    if ($plugin && $widget) {
      $form_state = array();
      $plugin->init()->build();
      self::addDefaultFields($plugin, $widget, $nid);
      self::addExtraSettings($plugin);
      self::buildWidgetForm($widget, $plugin, $form_state);
      self::buildThemeSelection($widget, $plugin, $form_state);
      self::buildParentThemeSettings($widget, $plugin, $form_state);
      $form = \Drupal::formBuilder()->getForm('Drupal\dynoblock\Form\DynoblockForm', $plugin->form);
      $html = render($form);
      $commands = array();
      _dynoblocks_get_commands($_POST['data'], $commands);
      return compact('html', 'commands');
    }
    else {
      return FALSE;
      watchdog('dynoblock', 'CANT FIND WIDGET ' . $type);
    }
  }

  public static function editForm($rid, $bid, $nid) {
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
          $html = render($form);
          $commands = array();
          _dynoblocks_get_commands($_POST['data'], $commands);
          return compact('html', 'commands');

        }
      }
    }
    return array(
      '#type' => 'markup',
      '#markup' => '',
    );
  }

  private static function addDefaultFields(&$form, $widget, $nid) {
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

  private static function addExtraSettings(&$form, $default_values = array()) {
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

  public static function buildWidgetForm($widget, &$form, &$form_state) {
    $id = $widget['id'];
    self::$widget = $widget;
    self::$form_state = $form_state;
    self::$widget_deltas[$id] = isset(self::$widget_deltas[$id]) && is_numeric(self::$widget_deltas[$id]) ? self::$widget_deltas[$id] + 1 : 0;
    $form->form['widget'] = array(
      '#type' => 'hidden',
      '#value' => $id,
    );
    $cardinality = isset($widget['form_settings']['cardinality']) ? $widget['form_settings']['cardinality'] : NULL;
    $variant_support = isset($widget['form_settings']['variant_support']) ? $widget['form_settings']['variant_support'] : NULL;
    if ($cardinality !== NULL) {
      $items = empty($form_state['input'][$id]) && !empty($form_state[$id]) ? $form_state[$id] : $form_state['input'][$id];
      $container_id = self::createId($widget, 'widget-group');
      $form->form[$id] = array(
        '#type' => 'container',
        '#tree' => TRUE,
        '#theme_wrappers' => array('dynoblock_tabledrag'),
        '#attributes' => array(
          'class' => array('widget-field-groups'),
          'id' => $container_id,
        ),
      );
      if ($cardinality == -1) {
        $cardinality = empty($items) ? 1 : count($items);
      }
      $add_another_name = $id . '[' . self::$widget_deltas[$id] . '][add]';
      $form->form[$id]['add_another'] = self::addAnotherBtn($container_id, $add_another_name);

      if (is_array($items)) {
        $items = array_values($items);
      }

      for ($i = 0; $i < $cardinality; $i++) {
        $sub_widget_id = self::createId($widget, $i . '-sub-widgets');
        self::$sub_widget_ids[$i] = $sub_widget_id;
        $form->form[$id][$i] = array(
          '#type' => 'container',
          '#attributes' => array(
            'class' => array('widget-field-group'),
            'id' => $sub_widget_id,
          ),
        );
        $form->form[$id][$i]['widget'] = $form->widgetForm($form_state, $items, $i);

        if ($cardinality > 1) {
          $name = $id . '[' . self::$widget_deltas[$id] . '][remove][' . $i . ']';
          $form->form[$id][$i]['widget']['items']['remove'] = self::removeItemBtn($id, $i, $container_id, $name);
        }

        if ($variant_support) {
          $form->form[$id][$i]['id'] = array(
            '#type' => 'hidden',
            '#default_value' => (empty($items[$i]['id']) ? DynoBlockForms::randId() :  $items[$i]['id']),
          );
        }
      }
    }
  }

  public static function randId() {
    return md5(random_bytes(32) . time());
  }

  public static function buildParentThemeSettings($widget, &$form, &$form_state) {
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

  public static function buildThemeSelection($widget, &$form, &$form_state) {
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
      $default = !empty($form_state['input']['theme']) ? $form_state['input']['theme'] : NULL;
      $default = !empty($form_state['theme_overview']['theme']) ? $form_state['theme_overview']['theme'] : $default;
      $default = !empty($form_state['theme']) ? $form_state['theme'] : $default;
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
          'callback' => self::fieldAjaxCallback,
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
        //if ($number_of_themes > 1 && empty($form_state['theme']) && !empty($form_state)) {
        if ($number_of_themes > 1) {
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

  public static function getPreview($widget) {
    if (!empty($widget->preview_image)) {
      $image_path = $widget->preview_image;
      return array(
        '#type' => 'markup',
        '#markup' => '<img height="280px" src="' . $image_path . '"/>',
      );
    }
  }

  public static function themeOptions(&$item, $delta, $values, $themes) {
    $container_id = !empty(self::$sub_widget_ids[$delta]) ? self::$sub_widget_ids[$delta] : 'widget-group-' . $delta;
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
        'callback' => self::fieldAjaxCallback,
        'wrapper' => $container_id,
        'method' => 'replace',
        'type' => 'sub_item_theme',
      ),
      '#attributes' => array(
        'delta' => $delta,
        'target' => $container_id,
      ),
    );
    if ($theme_selected && $theme_selected != 'default') {
      $theme = new $theme_selected($values);
      if ($number_of_themes > 1 && (self::$method != 'edit' || !empty(self::$form_state['dyno_system']['themes_selected'][$container_id]))) {
        $item['preview']['#value'] = $theme->preview();
      }
      if ($number_of_themes <= 1) {
        $item['theme'] = array(
          '#type' => 'hidden',
          '#value' => $themes['default'],
        );
      }
      $theme->form($item);
    }
    if (empty(self::$form_state['dyno_system']['themes_selected'][$container_id])) {
      self::$form_state['dyno_system']['themes_selected'][$container_id] = $theme_selected;
    }
  }

  public static function fieldOptions(&$form, $values, $wrapper, $fields, $delta = 0) {
    $fields_selected = !empty($values['field_options']) ? $values['field_options'] : array();
    $field_options = array();
    foreach ($fields as $key => $field) {
      $field_options[$field['handler'] . '|' . $field['field_name'] . '|' . $key] = $field['label'];
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
        'callback' => self::widgetSettingsCallback,
        'wrapper' => $wrapper,
        'method' => 'replace',
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
          $field_ids = list($class, $field_name, $delta) = explode('|', $class);
          $form[$field_name] = DynoWidgetAPI::element($values, $class, !empty($fields[$delta]['properties']) ? $fields[$delta]['properties'] : array());
        }
      }
    }
  }


  private static function addAnotherBtn($id, $name) {
    return array(
      '#type' => 'submit',
      '#submit' => array(self::coreAjaxSubmit),
      '#value' => t('Add Another'),
      '#weight' => 100,
      '#name' => $name,
      '#ajax' => array(
        'wrapper' => $id,
        'callback' => self::coreAjaxCallback,
        'method' => 'replace',
        'effect' => 'fade',
        'type' => 'add',
      ),
      '#attributes' => array(
        '#type' => 'add',
        'target' => $id,
      ),
    );
  }

  private static function removeItemBtn($widget, $delta, $ajax_target, $name) {
    return array(
      '#type' => 'submit',
      '#submit' => array(self::coreAjaxSubmit),
      '#value' => t('Remove'),
      '#weight' => 100,
      '#name' => $name,
      '#attributes' => array(
        '#type' => 'remove',
        'class' => array('btn-danger'),
        'target' => $ajax_target,
      ),
      '#ajax' => array(
        'wrapper' => $ajax_target,
        'callback' => self::coreAjaxCallback,
        'method' => 'replace',
        'effect' => 'fade',
        'type' => 'remove',
        'delta' => $delta,
        'widget' => $widget,
      ),
    );
  }

  public static function createId($widget, $type) {
    $id = !empty(self::$widget_deltas[$widget['id']]) ? self::$widget_deltas[$widget['id']] : 0;
    return $widget['id'] . '-' . $id . '-' . $type;
  }

}
