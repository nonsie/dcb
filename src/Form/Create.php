<?php

namespace Drupal\dynoblock\Form;

use Drupal\Core\Form\FormStateInterface;


/**
 * Simple wizard step form.
 */
class Create extends ComponentWizardBaseForm {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'dynoblock_admin_widget_create_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');

    $nid = '12345';

    self::$method = 'new';
    $core = $this->core;
    $plugin = $core->initPlugin($cached_values['selected_component']);
    $widget = $core->getWidget($cached_values['selected_component']);
    if ($plugin && $widget) {
      $plugin->init()->build();
      parent::addDefaultFields($plugin, $widget, $nid);
      parent::addExtraSettings($plugin);
      parent::buildWidgetForm($widget, $plugin, $form_state);
      parent::buildThemeSelection($widget, $plugin, $form_state);
      parent::buildParentThemeSettings($widget, $plugin, $form_state);
      $widgetForm = $plugin->form;
    }

    if (!empty($form_state->getUserInput('widget'))) {
      if ($plugin && $widget) {
        $plugin->rebuild = TRUE;
        $plugin->form = array();
        $plugin->init()->build($form_state);
        parent::buildWidgetForm($widget, $plugin, $form_state);
        parent::buildThemeSelection($widget, $plugin, $form_state);
        $widgetForm = array_replace($widgetForm, $plugin->form);
        $form_state->widget = $widget;
      }
    }

    $widgetForm['#token'] = FALSE;
    return $widgetForm;

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


  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
