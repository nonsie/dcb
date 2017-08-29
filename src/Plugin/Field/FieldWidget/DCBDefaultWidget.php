<?php

namespace Drupal\dcb\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @File: Plugin implementation of the 'dcb_default' field widget.
 */

/**
 * Plugin implementation of the 'dcb_default' widget.
 *
 * @FieldWidget(
 *   id = "dcb_default",
 *   label = @Translation("DCB input"),
 *   field_types = {
 *     "dcb"
 *   }
 * )
 */
class DCBDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $formState) {
    $value = isset($items[$delta]->id) ? $items[$delta]->id : '';

    $element['id'] = [
      '#type' => 'textfield',
      '#title' => t('DCB region ID'),
      '#default_value' => $value,
      '#size' => 60,
      '#element_validate' => [
        [$this, 'validate'],
      ],
    ];

    return $element;
  }

  /**
   * Validate dcb field.
   *
   * @param $element
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public function validate($element, FormStateInterface $formState) {
    $value = $element['#value'];
    if (strlen($value) == 0) {
      $formState->setValueForElement($element, '');
      return;
    }
    else {
      // DCB field cannot contain spaces.
      if (preg_match('/\s/', strtolower($value))) {
        $formState->setError($element, t("DCB field cannot contain spaces"));
      }
    }
  }

}
