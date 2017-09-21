<?php

/**
 * @File: Defines the default theme for a Page Title component
 */

namespace Drupal\dcb\Plugin\DCBComponent\PageTitle;

use Drupal\dcb\Base\Theme\DCBComponentTheme;

/**
 * Class PageTitleDefaultTheme.
 *
 * @package Drupal\dynoblock\Plugin\Dynoblock\PageTitle
 */
class PageTitleDefaultTheme extends DCBComponentTheme {

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

    $content = $values;

    return $content;
  }

  /**
   * @param string $file
   * @return mixed|null
   */
  public function preview($file = '') {
    if (empty($file)) {
      return parent::preview($this->plugin->themes['dcb-page-title-default']['previewImage']);
    }
    else {
      return parent::preview($file);
    }
  }
}
