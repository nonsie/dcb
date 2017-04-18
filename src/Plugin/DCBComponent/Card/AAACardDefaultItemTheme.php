<?php

/**
 * @File: Defines the default item theme for a single card.
 */

namespace Drupal\dcb\Plugin\DCBComponent\Card;

/**
 * Default Icon Card Theme.
 * Uses the default cards theme with a different preview image and class name.
 */
class AAACardDefaultItemTheme extends CardsDefaultTheme {

  /**
   * @param array $values
   * @param array $settings
   * @return array
   */
  public function display($values = [], $settings = []) {
    $content = parent::display($values, $settings);
    // Add class for AAAIconCardDefaultTheme.
    $content['columns']['#attributes']['class'][] = 'AAAIconCardDefaultTheme';
    // Remove class for AAACardsDefaultTheme.
    if (($key = array_search('AAACardsDefaultTheme', $content['columns']['#attributes']['class'])) !== FALSE) {
      unset($content['columns']['#attributes']['class'][$key]);
    }

    return $content;
  }

  /**
   * @param string $file
   * @return mixed|null
   */
  public function preview($file = '') {
    return parent::preview('card.png');
  }
}
