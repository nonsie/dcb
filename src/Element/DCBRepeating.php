<?php

namespace Drupal\dcb\Element;

use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Url;
use Drupal\dcb\Form\ComponentWizardBaseForm;

/**
 * Provides a repeating set of fields with "add another" button based on cardinality settings.
 * @RenderElement("dcb_repeating")
 */
class DCBRepeating extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return array(
      '#process' => array(
        array($class, 'processRepeatingContainer'),
        array($class, 'processGroup'),
      ),
      '#theme_wrappers' => array('dcb_repeating'),
      '#tree' => TRUE,
    );
  }

  public function processRepeatingContainer(&$element, FormStateInterface $form_state, &$complete_form) {
    $element = [
      'repeating_container' => [
        '#type' => 'container',
        '#attributes' => ['id' => 'repeat-forms'],
        'dcb-repeats' => self::getRepeats($element, $form_state),
        'controls' => [
          '#type' => 'submit',
          '#value' => $element['#more_button'],
          '#submit' => [[$element['#form_class'], 'cardinalitySubmit']],
          '#ajax' => array(
            'url' => Url::fromRoute('dcb.admin.wizard.ajax.step', ['step'=>'editform']),
            'options' => ['query' => \Drupal::request()->query->all() + [FormBuilderInterface::AJAX_FORM_REQUEST => TRUE]],
            'wrapper' => 'repeat-forms',
            'callback' => [$element['#form_class'], 'cardinalityCallback'],
            'method' => 'replaceWith',
            'effect' => 'fade',
            'type' => 'add',
          ),
          '#attributes' => array(
            '#type' => 'add',
            'data-drupal-target' => 'repeat-forms',
          ),
        ],
      ],
    ];

    return $element;
  }

  public static function getRepeats($element, FormStateInterface $form_state) {
    $count=0;
    $min = $element['#initial_cardinality'];
    $max = $element['#max_cardinality'];
    $current_items = !empty($form_state->getValue('dcb_repeats')) ? count($form_state->getValue('dcb_repeats')) : 0;

    if ($current_items < $min) {
      for($x=0; $x<$min; $x++) {
        $items[$x] = [
            '#type' => 'details',
            '#open' => FALSE,
            '#title' => t('Item @delta', array(
              '@delta' => ($x + 1),
            )),
          ] + $element['#repeat'];
      }
    }
    else {
      for($x=0; $x<$current_items; $x++) {
        $items[$x] = [
            '#type' => 'details',
            '#open' => FALSE,
            '#title' => t('Item @delta', array(
              '@delta' => ($x + 1),
            )),
          ] +$element['#repeat'];
      }
    }
    return $items;
  }

}