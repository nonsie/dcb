<?php

namespace Drupal\dcb\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ctools\Wizard\FormWizardBase;


/**
 * Simple wizard step form.
 */
class SelectGroup extends ComponentWizardBaseForm {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'dcb_admin_group_select_form';
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

    $expected_args = ['rid', 'bid', 'etype', 'eid'];
    $storage = &$form_state->getStorage();
    $storage['expected_args'] = $expected_args;
    $form_state->setStorage($storage);

    $this->setArgsFromURI($form_state);

    $themes = $this->core->getThemes();
    $selected_theme =  $cached_values['theme']['id'];

    foreach ($themes as $theme) {
      $options[$theme['id']] = $theme['label'] . ' - ' . $theme['description_short'];
    }

    $form['theme'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Select Component Group'),
      '#default_value' => $selected_theme,
      '#options' => $options,
    );

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

    $themes = $this->core->getThemes();
    $cached_values = $form_state->getTemporaryValue('wizard');
    $cached_values['theme'] =  $themes[$form_state->getValue('theme')];
    $form_state->setTemporaryValue('wizard', $cached_values);
    $storage = &$form_state->getStorage();
    foreach ($storage['expected_args'] as $arg) {
      $cached_values[$arg] = $form_state->get($arg);
    }
    $form_state->setTemporaryValue('wizard', $cached_values);
  }

}
