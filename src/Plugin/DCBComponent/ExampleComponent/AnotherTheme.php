<?php

namespace Drupal\dcb\Plugin\DCBComponent\ExampleComponent;

/**
 * Class PageTitleGray.
 *
 * @package Drupal\dynoblock\Plugin\Dynoblock\PageTitle
 */
class AnotherTheme extends ExampleDefaultTheme {

  public function display($values = array(), $settings = array()) {

    $content = parent::display($values, $settings);

    $content['wrapper']['#attributes']['class'] = array('columns-container-outer', 'container-fluid', 'gray-bg');

    return $content;

  }

  public function preview($file = '') {
    return parent::preview($this->plugin->themes['dcb-example-another']['preview_image']);
  }

}