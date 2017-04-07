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

    $form['box'] = [
      '#type' => 'markup',
      '#markup' => 'Theme:' . $cached_values['theme']['label'] . '<br/> widget:' . $cached_values['selected_component']. '<br/> region:' . $cached_values['rid'],
    ];

    return $form;
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
