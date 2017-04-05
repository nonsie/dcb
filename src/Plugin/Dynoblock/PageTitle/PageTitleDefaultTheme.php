<?php

namespace Drupal\dynoblock\Plugin\Dynoblock\PageTitle;

use Drupal\dynoblock\DynoWidgetTheme;

/**
 * Class PageTitleDefaultTheme.
 *
 * @package Drupal\dynoblock\Plugin\Dynoblock\PageTitle
 */
class PageTitleDefaultTheme extends DynoWidgetTheme {

  public function form(&$widget_form, $settings = array()) {

  }

  public function display($values = array(), $settings = array()) {

    $content = $values;

    return $content;
  }

  public function preview($file = '') {
    if(empty($file)) {
      return parent::preview($this->plugin->themes['dynoblock-page-title-default']['preview_image']);
    } else {
      return parent::preview($file);
    }
  }
}
