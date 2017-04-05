<?php

namespace Drupal\dynoblock\Plugin\Dynoblock\Card;

/**
 * Default Icon Card Theme.
 * Uses the default cards theme with a different preview image and class name.
 */
class AAACardDefaultItemTheme extends CardsDefaultTheme {

  public function display($values = array(), $settings = array()) {
    $content = parent::display($values, $settings);
    // Add class for AAAIconCardDefaultTheme.
    $content['columns']['#attributes']['class'][] = 'AAAIconCardDefaultTheme';
    // Remove class for AAACardsDefaultTheme.
    if (($key = array_search('AAACardsDefaultTheme', $content['columns']['#attributes']['class'])) !== FALSE) {
      unset($content['columns']['#attributes']['class'][$key]);
    }

    return $content;
  }

  public function preview($file = '') {
    return parent::preview('card.png');
  }
}
