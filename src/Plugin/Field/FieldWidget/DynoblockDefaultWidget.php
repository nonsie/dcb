<?php

namespace Drupal\dynoblock\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'dynoblock_default' widget.
 *
 * @FieldWidget(
 *   id = "dynoblock_default",
 *   label = @Translation("Dynoblock input"),
 *   field_types = {
 *     "dynoblock"
 *   }
 * )
 */
class DynoblockDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = isset($items[$delta]->id) ? $items[$delta]->id : '';

    $element['id'] = array(
      '#type' => 'textfield',
      '#title' => t('Dynoblock region ID'),
      '#default_value' => $value,
      '#size' => 60,
      '#element_validate' => array(
        array($this, 'validate'),
      ),
    );

    return $element;
  }

  /**
   * Validate dynoblock field.
   */
  public function validate($element, FormStateInterface $form_state) {
    $value = $element['#value'];
    if (strlen($value) == 0) {
      $form_state->setValueForElement($element, '');
      return;
    }
    else {
      // Dynoblock field cannot contain spaces.
      if (preg_match('/\s/', strtolower($value))) {
        $form_state->setError($element, t("Dynoblock field cannot contain spaces"));
      }
    }
  }
}
