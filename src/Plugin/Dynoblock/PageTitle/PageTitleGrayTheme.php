<?php

namespace Drupal\dynoblock\Plugin\Dynoblock\PageTitle;

/**
 * Class PageTitleGray.
 *
 * @package Drupal\dynoblock\Plugin\Dynoblock\PageTitle
 */
class PageTitleGrayTheme extends PageTitleDefaultTheme {

  public function display($values = array(), $settings = array()) {

    $content = parent::display($values, $settings);

    $content['wrapper']['#attributes']['class'] = array('columns-container-outer', 'container-fluid', 'gray-bg');

    return $content;

  }

  public function preview($file = '') {
    return parent::preview($this->plugin->themes['dynoblock-page-title-gray']['preview_image']);
  }

}
