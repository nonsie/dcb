<?php

namespace Drupal\dynoblock\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'dynoblock_default' formatter.
 *
 * @FieldFormatter(
 *   id = "dynoblock_default",
 *   label = @Translation("Dynoblock default formatter"),
 *   field_types = {
 *     "dynoblock"
 *   }
 * )
 */
class DynoblockDefaultFormatter extends FormatterBase {

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
    $dynoblockCore = \Drupal::service('dynoblock.core');
    $element = array();
    foreach ($items as $delta => $item) {
      $element[$delta] = $dynoblockCore->dynoRegion($item->id, $item->getEntity()->id(), ucfirst($item->id));
      $element[$delta]['blocks'] = $dynoblockCore->renderDynoBlocks($item->id, $item);
    }
    return $element;
  }

}
