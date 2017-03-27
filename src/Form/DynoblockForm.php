<?php

namespace Drupal\dynoblock\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MembershipFormBase.
 *
 * @package Drupal\aaa_membership\Form
 */
class DynoblockForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dynoblock_form';
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $widgetForm = array()) {
    if (!empty($form_state->rebuild) && !empty($form_state->input['widget'])) {
      $core = \Drupal::service('dynoblock.core');
      $handler = _dynoblock_find_form_handler($form_state->input['widget']);
      $widget = $core->getWidget($form_state->input['widget']);
      if ($handler && $widget) {
        $handler->rebuild = TRUE;
        $handler->form = array();
        $handler->init()->build($form_state);
        DynoBlockForms::buildWidgetForm($widget, $handler, $form_state);
        DynoBlockForms::buildThemeSelection($widget, $handler, $form_state);
        $widgetForm = array_replace($widgetForm, $handler->form);
        $form_state->widget = $widget;
      }
    }
    $form = $widgetForm;
    return $widgetForm;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
