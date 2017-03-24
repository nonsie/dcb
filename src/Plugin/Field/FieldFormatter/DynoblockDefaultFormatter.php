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
    $element = array();
    foreach ($items as $delta => $item) {
      // Render each element as markup.
      $element[$delta] = array(
        '#type' => 'container',
        '#attributes' => array(
          'class' => array('dynoblock-region'),
          'data-dyno-rid' => $item->id,
          'data-dyno-label' => $item->getEntity()->bundle(),
           // Note: this was called data-dyno-nid in D7.
          'data-dyno-eid' => $item->getEntity()->id(),
        ),
      );
    }
    return $element;
  }

}
