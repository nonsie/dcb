<?php

namespace Drupal\dynoblock\Form;

use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ctools\Wizard\FormWizardBase;
use Drupal\dynoblock\DynoBlockForms;

/**
 * Simple wizard step form.
 */
class SelectWidget extends ComponentWizardBaseForm {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'dynoblock_admin_widget_select_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\ctools\Wizard\FormWizardBase $wizard
   *   The wizard form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, FormWizardBase $wizard = NULL) {
    $cached_values = $form_state->getTemporaryValue('wizard');

    if(!empty($form_state->getValue('selected_component'))) {
      $cached_values['selected_component'] = $form_state->getValue('selected_component');
    }

    $selected_component =
      (isset($cached_values['selected_component']) && !empty($cached_values['selected_component']))
        ? $cached_values['selected_component'] : '';

    $widgets = $this->core->loadWidgets();
    $options[''] = 'Select One';
    foreach ($widgets as $machine => &$component) {
      $options[$machine] = $component['name'];
    }

    $form['#attributes']['id'][]='special-wrapper';

    $parameters = $wizard->getNextParameters($cached_values);
    $parameters['step'] = $wizard->getStep($cached_values);
    $form['selected_component'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a component type:'),
      '#default_value' => $selected_component,
      '#options' => $options,
      '#ajax' => [
        'url' => Url::fromRoute('dynoblock.admin.wizard.ajax.step', $parameters),
        'options' => ['query' => \Drupal::request()->query->all() + [FormBuilderInterface::AJAX_FORM_REQUEST => TRUE]],
        'callback' => [$this, 'ajaxPreviewCallback'],
        'wrapper' => 'preview-container',
      ],
    ];

    $form['preview_placeholder'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'preview-container',
      ],
    ];

    if(!empty($selected_component)) {
      $layout = $this->core->initPlugin($selected_component);
      $preview = DynoBlockForms::getPreview($layout);
      $form['preview_placeholder'] += [
        'preview_description' => [
          '#type' => 'container',
          '#attributes' => [
            'class' => [
              'preview-title'
            ],
          ],
          'name' => [
            '#type' => 'markup',
            '#markup' => $this->t("Preview for: @component", ['@component' => $widgets[$selected_component]['name']]),
          ],
          'description' => [
            '#type' => 'html_tag',
            '#tag' => 'p',
            '#value' => $widgets[$selected_component]['description_short'],
          ],
        ],
        'preview_image' => $preview,
      ];
    }

    return $form;
  }

  public function ajaxPreviewCallback(array &$form, FormStateInterface $form_state) {
    return $form['preview_placeholder'];
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
    $cached_values = $form_state->getTemporaryValue('wizard');
    $cached_values['selected_component'] =  $form_state->getValue('selected_component');
    $form_state->setTemporaryValue('wizard', $cached_values);
  }

}
