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
    $value = isset($items[$delta]->value) ? $items[$delta]->value : NULL;

    $element += array(
      '#type' => 'textfield',
      '#default_value' => $value,
      '#placeholder' => $this->getSetting('placeholder'),
    );

    return array('value' => $element);
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
    'size' => 60,
    'placeholder' => '',
    ) + parent::defaultSettings();
  }
}
