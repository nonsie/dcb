<?php

namespace Drupal\dcb\Wizard;


use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ctools\Wizard\FormWizardBase;

class AdminFormWizard extends FormWizardBase {

  public $rid;

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
  public function getRouteName() {
    return 'dcb.admin.wizard.ajax.step';
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations($cached_values) {
    $operations = array(
      'selectgroup' => [
        'form' => 'Drupal\dcb\Form\SelectGroup',
        'title' => $this->t('Select a Group'),
      ],
      'selectwidget' => [
        'form' => 'Drupal\dcb\Form\SelectWidget',
        'title' => $this->t('Select a widget'),
      ],
      'editform' => [
        'form' => 'Drupal\dcb\Form\Create',
        'title' => $this->t('Create'),
      ]
    );

    return $operations;
  }

  /**
   * We have to duplicate code so $this can be passed into buildForm().
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    // Get the current form operation.
    $operation = $this->getOperation($cached_values);
    $form = $this->customizeForm($form, $form_state);
    /* @var $formClass \Drupal\Core\Form\FormInterface */
    $formClass = $this->classResolver->getInstanceFromDefinition($operation['form']);
    // Pass include any custom values for this operation.
    if (!empty($operation['values'])) {
      $cached_values = array_merge($cached_values, $operation['values']);
      $form_state->setTemporaryValue('wizard', $cached_values);
    }
    // Build the form.
    $form = $formClass->buildForm($form, $form_state, $this);
    if (isset($operation['title'])) {
      $form['#title'] = $operation['title'];
    }
    $form['actions'] = $this->actions($formClass, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function finish(array &$form, FormStateInterface $form_state) {
    parent::finish($form, $form_state);
  }

  /**
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public function getPrevOp() {
    return $this->t('Previous');
  }

  /**
   * {@inheritdoc}
   * Override parent method to add our own commands when the wizard finishes.
   */
  public function ajaxFinish(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $newCommand = $form_state->getValue('ajaxcommand');
    $response->addCommand(new CloseModalDialogCommand());
    $response->addCommand($newCommand);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function previous(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue($this->getMachineName());
    // TODO: need to save values in $cached_values before going back.
    $parameters = $this->getPreviousParameters($cached_values);
    if (!$form_state->get('ajax')) {
      $form_state->setRedirect($this->getRouteName(), $parameters);
    }
    else {
      if (!empty($parameters['step'])) {
        $this->step = $parameters['step'];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $cached_values = $this->getTempstore()->get($this->getMachineName());
    if ($form_state->get('ajax')) {
      if ((string)$form_state->getValue('op') == (string)$this->getNextOp()) {
        $parameters = $this->getNextParameters($cached_values);
        if (!empty($parameters['step'])) {
          $this->step = $parameters['step'];
        }
      }
    }
  }

}
