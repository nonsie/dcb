<?php

namespace Drupal\dcb\Plugin\DCBComponent\Card;

/**
 * Default Icon Card Theme.
 * Uses the default cards theme with a different preview image and class name.
 */
class AAACardTextCenterItemTheme extends CardsDefaultTheme {

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
    return parent::preview('icon_card_centered.png');
  }
}
