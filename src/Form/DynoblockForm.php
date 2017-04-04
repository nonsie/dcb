<?php

namespace Drupal\dynoblock\Form;

use Behat\Mink\Exception\Exception;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dynoblock\DynoBlockForms;
use Drupal\Component\Utility\NestedArray;

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
  public function buildForm(array $form, FormStateInterface $form_state, array $widgetForm = array()) {
    if (!empty($form_state->getUserInput('widget'))) {
      $core = \Drupal::service('dynoblock.core');
      $handler = $core->initPlugin($form_state->getUserInput('widget')['widget']);
      $widget = $core->getWidget($form_state->getUserInput('widget')['widget']);
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
    return $widgetForm;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  public function cardinalityCallback(array &$form, FormStateInterface &$form_state) {
    $trigger = $form_state->getTriggeringElement();
    switch ($trigger['#ajax']['type']) {
      case 'add':
        return $form[$form_state->getValue('widget')];
        break;
      case 'remove':
        return $form[$form_state->getValue('widget')];
        break;
    }
  }

  public function cardinalitySubmit(array &$form, FormStateInterface &$form_state) {
    $form_state->setRebuild(TRUE);
    $trigger = $form_state->getTriggeringElement();
    $type = $trigger['#attributes']['#type'];
    $widget = $form_state->getUserInput('widget');
    switch ($type) {
      case 'remove':
        $storage = &$form_state->getStorage();
        if(isset($storage['sub_widgets_amount'])) {
          $storage['sub_widgets_amount']--;
        } else {
          $storage['sub_widgets_amount'] = 0;
        }
        $form_state->setStorage($storage);
        break;
      case 'add':
        $storage = &$form_state->getStorage();
        if(isset($storage['sub_widgets_amount'])) {
          $storage['sub_widgets_amount']++;
        } else {
          $storage['sub_widgets_amount'] = 1;
        }
        $form_state->setStorage(array('sub_widgets_amount' => $storage['sub_widgets_amount']));
        break;
    }
  }

}
