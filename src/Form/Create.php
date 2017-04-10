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
    self::$method = 'new';
    $core = $this->core;

    if (!empty($form_state->getUserInput('widget')['widget'])) {
      $handler = $core->initPlugin($form_state->getUserInput('widget')['widget']);
      $widget = $core->getWidget($form_state->getUserInput('widget')['widget']);
      if ($handler && $widget) {
        $handler->rebuild = TRUE;
        $handler->form = array();
        $handler->init()->build($form_state);
        $this->addDefaultFields($handler, $widget, $nid);
        $this->addExtraSettings($handler);
        $this->buildWidgetForm($widget, $handler, $form_state);
        $this->buildThemeSelection($widget, $handler, $form_state);
        $this->buildParentThemeSettings($widget, $handler, $form_state);
        $widgetForm = $handler->form;
        //$form_state->widget = $widget;
      }
    }
    else {
      $plugin = $core->initPlugin($cached_values['selected_component']);
      $widget = $core->getWidget($cached_values['selected_component']);
      if ($plugin && $widget) {
        $plugin->init()->build();
        $this->addDefaultFields($plugin, $widget, $nid);
        $this->addExtraSettings($plugin);
        $this->buildWidgetForm($widget, $plugin, $form_state);
        $this->buildThemeSelection($widget, $plugin, $form_state);
        $this->buildParentThemeSettings($widget, $plugin, $form_state);
        $widgetForm = $plugin->form;
      }
    }

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

  }

}
