<?php

namespace Drupal\dynoblock\Form;

use Behat\Mink\Exception\Exception;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dynoblock\DynoBlockForms;

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
    if (!empty($form_state->getUserInput('widget'))) {
      $core = \Drupal::service('dynoblock.core');
      $handler = $core->initPlugin($form_state->getUserInput('widget')['widget']);
      $widget = $core->getWidget($form_state->getUserInput('widget')['widget']);
      //throw new \Symfony\Component\Config\Definition\Exception\Exception(print_r($handler, TRUE));
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
