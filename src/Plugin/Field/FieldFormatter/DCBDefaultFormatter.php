<?php

/**
 * @File: Plugin implementation of the 'dcb_default' formatter.
 */

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
    $summary = [];
    $summary[] = t('Displays DCB region ID.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    /** @var \Drupal\dcb\Service\DCBCore $DCBCore */
    $DCBCore = \Drupal::service('dcb.core');

    $element = [];
    foreach ($items as $delta => $item) {
      //$element[$delta] = $renderer->renderRegion($item->id, $item->getEntity()->id(), ucfirst($item->id));
      //$element[$delta]['components'] = $DCBCore->renderComponents($item->id, $item, $renderer);
      $element[$delta] = $DCBCore->renderRegion($item->id, $item->getEntity()->id(), ucfirst($item->id), 'drupal_theme_renderer');
    }
    return $element;
  }

}
