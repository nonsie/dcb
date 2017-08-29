<?php

namespace Drupal\dcb\Wizard;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ctools\Wizard\FormWizardBase;

/**
 * Class AdminFormWizard.
 *
 * @package Drupal\dcb\Wizard.
 */
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
    return $this->t('DCB Admin Form Wizard');
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
    $operations = [
      'selectgroup' => [
        'form' => 'Drupal\dcb\Form\SelectGroup',
        'title' => $this->t('Select a group'),
      ],
      'selectwidget' => [
        'form' => 'Drupal\dcb\Form\SelectWidget',
        'title' => $this->t('Select a widget'),
      ],
      'editform' => [
        'form' => 'Drupal\dcb\Form\Create',
        'title' => $this->t('Create'),
      ],
    ];

    return $operations;
  }

  /**
   * We have to duplicate code so $this can be passed into buildForm().
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $formState) {
    $cached_values = $formState->getTemporaryValue('wizard');
    // Get the current form operation.
    $operation = $this->getOperation($cached_values);
    $form = $this->customizeForm($form, $formState);
    /* @var $formClass \Drupal\Core\Form\FormInterface */
    $formClass = $this->classResolver->getInstanceFromDefinition($operation['form']);
    // Pass include any custom values for this operation.
    if (!empty($operation['values'])) {
      $cached_values = array_merge($cached_values, $operation['values']);
      $formState->setTemporaryValue('wizard', $cached_values);
    }
    // Build the form.
    $form = $formClass->buildForm($form, $formState, $this);
    if (isset($operation['title'])) {
      $form['#title'] = $operation['title'];
    }
    $form['actions'] = $this->actions($formClass, $formState);
    return $form;
  }

  /**
   * Gets the display name of the "back" button.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The translated string for the display of the back button.
   */
  public function getPrevOp() {
    return $this->t('Previous');
  }

  /**
   * {@inheritdoc}
   *
   * Override parent method to add our own commands when the wizard finishes.
   */
  public function ajaxFinish(array $form, FormStateInterface $formState) {
    $response = new AjaxResponse();
    $newCommand = $formState->getValue('ajaxcommand');
    $response->addCommand(new CloseModalDialogCommand());
    $response->addCommand($newCommand);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function previous(array &$form, FormStateInterface $formState) {
    $cached_values = $formState->getTemporaryValue($this->getMachineName());
    $parameters = $this->getPreviousParameters($cached_values);
    if (!$formState->get('ajax')) {
      $formState->setRedirect($this->getRouteName(), $parameters);
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
  public function submitForm(array &$form, FormStateInterface $formState) {
    parent::submitForm($form, $formState);
    $cached_values = $this->getTempstore()->get($this->getMachineName());
    if ($formState->get('ajax')) {
      if ((string) $formState->getValue('op') == (string) $this->getNextOp()) {
        $parameters = $this->getNextParameters($cached_values);
        if (!empty($parameters['step'])) {
          $this->step = $parameters['step'];
        }
      }
    }
  }

}
