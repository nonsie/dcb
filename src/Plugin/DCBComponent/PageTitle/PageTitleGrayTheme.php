<?php

/**
 * @File: Defines the Gray theme for a Page Title component
 */

namespace Drupal\dcb\Plugin\DCBComponent\PageTitle;

/**
 * Class PageTitleGrayTheme
 * @package Drupal\dcb\Plugin\DCBComponent\PageTitle
 */
class PageTitleGrayTheme extends PageTitleDefaultTheme {

  /**
   * @param array $values
   * @param array $settings
   * @return array
   */
  public function display($values = [], $settings = []) {

    $content = parent::display($values, $settings);

    return $content;

  }

  /**
   * @param string $file
   * @return mixed|null
   */
  public function preview($file = '') {
    return parent::preview($this->plugin->themes['dcb-page-title-gray']['previewImage']);
  }

}
