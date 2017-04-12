<?php

namespace Drupal\dynoblock\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ctools\Wizard\FormWizardBase;


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
  public function buildForm(array $form, FormStateInterface $form_state, FormWizardBase $wizard = NULL) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    $this->initwizard($wizard, $form_state);
    $nid = '12345';
    $this->method = 'new';
    $core = $this->core;

    if (!empty($form_state->getUserInput()['widget'])) {
      $handler = $core->initPlugin($form_state->getUserInput()['widget']);
      $widget = $core->getWidget($form_state->getUserInput()['widget']);
      if ($handler && $widget) {
        $handler->rebuild = TRUE;
        $handler->form = array();
        $handler->init()->build($this);
        $this->buildWidgetForm($widget, $handler, $form_state);
        $this->buildThemeSelection($widget, $handler, $form_state);
        $this->buildParentThemeSettings($widget, $handler, $form_state);
        $this->addDefaultFields($handler, $widget, $nid);
        $this->addExtraSettings($handler);
        $widgetForm = $handler->form;
      }
    }
    else {
      $plugin = $core->initPlugin($cached_values['selected_component']);
      $widget = $core->getWidget($cached_values['selected_component']);
      if ($plugin && $widget) {
        $plugin->init()->build($this);
        $this->buildWidgetForm($widget, $plugin, $form_state);
        $this->buildThemeSelection($widget, $plugin, $form_state);
        $this->buildParentThemeSettings($widget, $plugin, $form_state);
        $this->addDefaultFields($plugin, $widget, $nid);
        $this->addExtraSettings($plugin);
        $widgetForm = $plugin->form;
      }
    }

    // TODO: If form_state is not unset here, ajax errors occur with complicated forms.
    unset($this->form_state);

    return $widgetForm;

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
    $bid = time();
    $cached_values = $form_state->getTemporaryValue('wizard');

    $data['widget'] = $form_state->getValue('widget');
    $data['fields'] = (!empty($form_state->getValue('fields')) ? $form_state->getValue('fields') : array());
    $data['theme'] = $form_state->getValue('theme');
    $data[$data['widget']] = (!empty($form_state->getValue($data['widget'])) ? $form_state->getValue($data['widget']) : array());
    $data['nid'] = $form_state->getValue('nid');
    $data['bid'] = $bid;
    $data['rid'] = $cached_values['rid'];

    $weight = $form_state->getValue('weight');

    $conditions['condition_token'] = $form_state->getValue('condition_token');
    $conditions['condition_value'] = $form_state->getValue('value');
    $conditions['condition_operators'] = $form_state->getValue('operators');

    $record = [
      'rid' => $cached_values['rid'],
      'bid' => $bid,
      'data' => serialize($data),
      'conditions' => serialize($conditions),
      'weight' => $weight,
    ];

    $this->core->db->save($record);
  }

}
