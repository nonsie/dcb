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

    $summary[] = t('Displays dynoblock ID.');

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
        '#type' => 'markup',
        '#markup' => serialize($item),
      );
    }

    return $element;
  }

}
