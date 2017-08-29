<?php

namespace Drupal\dcb\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ctools\Wizard\FormWizardBase;

/**
 * Class SelectGroup.
 *
 * @File: Step 1 of admin wizard. Allows admin to select component group.
 *
 * @package Drupal\dcb\Form
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
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current state of the form.
   * @param \Drupal\ctools\Wizard\FormWizardBase|null $wizard
   *   The form base.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $formState, FormWizardBase $wizard = NULL) {
    $cached_values = $formState->getTemporaryValue('wizard');

    $expected_args = ['rid', 'bid', 'etype', 'eid'];
    $storage = &$formState->getStorage();
    $storage['expected_args'] = $expected_args;
    $formState->setStorage($storage);

    $this->setArgsFromUri($formState);

    $themes = $this->core->getThemes();
    $selected_theme = isset($cached_values['theme']['id']) ? $cached_values['theme']['id'] : NULL;

    foreach ($themes as $theme) {
      $options[$theme['id']] = $theme['label'] . ' - ' . $theme['description_short'];
    }

    $form['theme'] = [
      '#type' => 'radios',
      '#title' => $this->t('Select Component Group'),
      '#default_value' => $selected_theme,
      '#options' => $options,
    ];

    return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $formState) {

    $themes = $this->core->getThemes();
    $cached_values = $formState->getTemporaryValue('wizard');
    $cached_values['theme'] = $themes[$formState->getValue('theme')];
    $formState->setTemporaryValue('wizard', $cached_values);
    $storage = &$formState->getStorage();
    foreach ($storage['expected_args'] as $arg) {
      $cached_values[$arg] = $formState->get($arg);
    }
    $formState->setTemporaryValue('wizard', $cached_values);
  }

}
