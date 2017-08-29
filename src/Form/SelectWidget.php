<?php

namespace Drupal\dcb\Form;

use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ctools\Wizard\FormWizardBase;

/**
 * @File: Step 2 of the wizard. Allows admin to select a component.
 */

/**
 * Class SelectWidget.
 *
 * @package Drupal\dcb\Form
 */
class SelectWidget extends ComponentWizardBaseForm {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'dcb_admin_widget_select_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current state of the form.
   * @param \Drupal\ctools\Wizard\FormWizardBase $wizard
   *   The wizard form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $formState, FormWizardBase $wizard = NULL) {
    $cached_values = $formState->getTemporaryValue('wizard');

    if (!empty($formState->getValue('selected_component'))) {
      $cached_values['selected_component'] = $formState->getValue('selected_component');
    }

    $selected_component =
      (isset($cached_values['selected_component']) && !empty($cached_values['selected_component']))
        ? $cached_values['selected_component'] : '';

    $widgets = $this->core->loadWidgets();
    $options[''] = 'Select One';
    foreach ($widgets as $machine => &$component) {
      $options[$machine] = $component['name'];
    }

    $parameters['step'] = $wizard->getStep($cached_values);
    $form['selected_component'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a component type:'),
      '#default_value' => $selected_component,
      '#options' => $options,
      '#ajax' => [
        'url' => Url::fromRoute('dcb.admin.wizard.ajax.step', $parameters),
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

    if (!empty($selected_component)) {
      $layout = $this->core->initPlugin($selected_component);
      $preview = $this->getPreview($layout);
      $form['preview_placeholder'] += [
        'preview_description' => [
          '#type' => 'container',
          '#attributes' => [
            'class' => [
              'preview-title',
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
        'previewImage' => $preview,
      ];
    }

    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   * @return mixed
   */
  public function ajaxPreviewCallback(array &$form, FormStateInterface $formState) {
    return $form['preview_placeholder'];
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
    $cached_values = $formState->getTemporaryValue('wizard');
    $cached_values['selected_component'] = $formState->getValue('selected_component');
    $formState->setTemporaryValue('wizard', $cached_values);
  }

}
