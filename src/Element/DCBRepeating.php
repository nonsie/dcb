<?php

namespace Drupal\dcb\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;

/**
 * Provides a repeating set of fields with "add another" button based on cardinality settings.
 * @RenderElement("dcb_repeating")
 */
class DCBRepeating extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    \Drupal::logger('dcb')->notice('fired');
    $class = get_class($this);
    return array(
      '#process' => array(
        array($class, 'processRepeatingContainer'),
        array($class, 'processGroup'),
      ),
      '#theme_wrappers' => array('dcb_repeating'),
    );
  }




  public function processRepeatingContainer(&$element, FormStateInterface $form_state, &$complete_form) {

    $element['repeat_container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'repeat-forms'],
      'dcb-repeats' => [
        '#type' => 'markup',
        '#markup' => 'blah',
      ],
      'controls' => [
        '#type' => 'submit',
        '#value' => 'submit',
      ],
    ];

    ksm($element, 'element');
    ksm($complete_form, 'complete form');
    ksm($form_state->getValues(), "form_state values");

    return $element;
  }

}