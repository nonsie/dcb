<?php

namespace Drupal\dynoblock\Wizard;


use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\ctools\Wizard\FormWizardBase;
use Drupal\user\SharedTempStoreFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AdminFormWizard extends FormWizardBase {

  protected $rid;

  public function __construct(SharedTempStoreFactory $tempstore, FormBuilderInterface $builder, ClassResolverInterface $class_resolver, EventDispatcherInterface $event_dispatcher, RouteMatchInterface $route_match, $tempstore_id, $machine_name = NULL, $step = NULL, $rid = NULL) {
    $this->rid = $rid;
    parent::__construct($tempstore,  $builder,  $class_resolver,  $event_dispatcher,  $route_match, $tempstore_id, $machine_name, $step);
  }

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
    return 'dynoblock.admin.wizard.ajax.step';
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations($cached_values) {
    $operations = array(
      'selectgroup' => [
        'form' => 'Drupal\dynoblock\Form\SelectGroup',
        'title' => $this->t('Select a Group'),
      ],
      'selectwidget' => [
        'form' => 'Drupal\dynoblock\Form\SelectWidget',
        'title' => $this->t('Select a widget'),
      ],
      'editform' => [
        'form' => 'Drupal\dynoblock\Form\Create',
        'title' => $this->t('Create'),
      ]
    );

    if ($this->step == 'selectgroup' && $this->rid != 'none') {
      $operations['selectgroup']['values']['rid'] = $this->rid;
    }

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
