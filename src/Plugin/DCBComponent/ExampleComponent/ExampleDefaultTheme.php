<?php

namespace Drupal\dcb\Plugin\DCBComponent\ExampleComponent;

use Drupal\dcb\DCBComponentTheme;

/**
 * Class PageTitleDefaultTheme.
 *
 * @package Drupal\dynoblock\Plugin\Dynoblock\PageTitle
 */
class ExampleDefaultTheme extends DCBComponentTheme {

  public function form(&$widget_form, $settings = array()) {

  }

  public function display($values = array(), $settings = array()) {

    $content = $values;

    return $content;
  }

  public function preview($file = '') {
    if(empty($file)) {
      return parent::preview($this->plugin->themes['dcb-example-default']['preview_image']);
    } else {
      return parent::preview($file);
    }
  }
}
