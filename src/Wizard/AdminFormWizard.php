<?php

namespace Drupal\dynoblock\Wizard;


use Drupal\Core\Form\FormStateInterface;
use Drupal\ctools\Wizard\FormWizardBase;

class AdminFormWizard extends FormWizardBase {

  /**
   * {@inheritdoc}
   */
  public function getWizardLabel() {
    return $this->t('Wizard Information');
  }

  /**
   * {@inheritdoc}
   */
  public function getMachineLabel() {
    return $this->t('Wizard Test Name');
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations($cached_values) {
    return array(
      'selectgroup' => [
        'form' => 'Drupal\dynoblock\Form\AdminSteps\SelectGroup',
        'title' => $this->t('Select a Group'),
        'values' => ['dynamic' => 'Xylophone'],
        'validate' => ['::stepOneValidate'],
        'submit' => ['::stepOneSubmit'],
      ],
      'selectwidget' => [
        'form' => 'Drupal\dynoblock\Form\AdminSteps\SelectWidget',
        'title' => $this->t('Select a widget'),
        'values' => ['dynamic' => 'Zebra'],
      ],
    );
  }

  /**
   * Validation callback for the first step.
   */
  public function stepOneValidate($form, FormStateInterface $form_state) {
    if ($form_state->getValue('one') == 'wrong') {
      $form_state->setErrorByName('one', $this->t('Cannot set the value to "wrong".'));
    }
  }

  /**
   * Submission callback for the first step.
   */
  public function stepOneSubmit($form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    if ($form_state->getValue('one') == 'magic') {
      $cached_values['one'] = 'Abraham';
    }
    $form_state->setTemporaryValue('wizard', $cached_values);
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteName() {
    return 'dynoblock.admin.wizard.step';
  }

  /**
   * {@inheritdoc}
   */
  public function finish(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    //drupal_set_message($this->t('Value One: @one', ['@one' => $cached_values['one']]));
    //drupal_set_message($this->t('Value Two: @two', ['@two' => $cached_values['two']]));
    parent::finish($form, $form_state);
  }

}