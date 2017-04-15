<?php

namespace Drupal\dcb\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'dcb_default' formatter.
 *
 * @FieldFormatter(
 *   id = "dcb_default",
 *   label = @Translation("DCB default formatter"),
 *   field_types = {
 *     "dcb"
 *   }
 * )
 */
class DCBDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();
    $settings = $this->getSettings();
    $summary[] = t('Displays dynoblock region ID.');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $dcbCore = \Drupal::service('dcb.core');
    $element = array();
    foreach ($items as $delta => $item) {
      $element[$delta] = $dcbCore->DCBRegion($item->id, $item->getEntity()->id(), ucfirst($item->id));
      $element[$delta]['blocks'] = $dcbCore->renderComponents($item->id, $item);
    }
    return $element;
  }

}
