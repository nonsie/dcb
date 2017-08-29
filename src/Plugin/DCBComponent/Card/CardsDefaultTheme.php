<?php

namespace Drupal\dcb\Plugin\DCBComponent\Card;

use Drupal\dcb\DCBComponentTheme;

/**
 * Class AAACardsDefaultTheme.
 *
 * @package Drupal\dynoblock\Plugin\Dynoblock\Card
 */
class CardsDefaultTheme extends DCBComponentTheme {

  /**
   * @param $widget_form
   * @param array $settings
   */
  public function form(&$widget_form, $settings = []) {
  }

  /**
   * @param array $values
   * @param array $settings
   * @return array
   */
  public function display($values = [], $settings = []) {
    return $values;
  }

  /**
   * @param string $file
   * @return mixed|null
   */
  public function preview($file = '') {
    if (empty($file)) {
      return parent::preview($this->plugin->themes['dcb-cards-default']['previewImage']);
    }
    else {
      return parent::preview($file);
    }
  }

}
