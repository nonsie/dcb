<?php

namespace Drupal\dynoblock\Plugin\Dynoblock\PageTitle;

use Drupal\dynoblock\DynoWidgetTheme;

/**
 * Class PageTitleDefaultTheme.
 *
 * @package Drupal\dynoblock\Plugin\Dynoblock\PageTitle
 */
class PageTitleDefaultTheme extends DynoWidgetTheme {

  public function form(&$widget_form, $settings = array()) {}

  public function display($values = array(), $settings = array()) {

    $content = array(
      'wrapper' => array(
        '#type' => 'container',
        '#tag' => 'section',
        '#attributes' => array(
          'class' => array('columns-container-outer', 'container-fluid'),
        ),
        'row' => array(
          '#type' => 'container',
          '#attributes' => array(
            'class' => array( "row" ),
          ),
          'inner' => array(
            '#type' => 'container',
            '#attributes' => array(
              'class' => array('columns-container-inner', 'container', 'one-column', 'section-title', 'centered'),
            ),
            'row' => array(
              '#type' => 'container',
              '#attributes' => array(
                'class' => array( "row" ),
              ),
              'col' => array(
                '#type' => 'container',
                '#attributes' => array(
                  'class' => array('column', 'col-xs-12'),
                ),
                'block' => array(
                  '#type' => 'container',
                  '#attributes' => array(
                    'class' => array('column-block', 'centered'),
                  ),
                  'copy' => array(
                    '#type' => 'html_tag',
                    '#tag' => 'h1',
                    '#value' => $values['title'],
                    '#attributes' => array(
                      'class' => array('column-title'),
                    ),
                  ),
                  'subtitle' => array(
                    '#type' => 'html_tag',
                    '#tag' => 'h4',
                    '#value' => !empty($values['subtitle']) ? $values['subtitle'] : NULL,
                    '#attributes' => array(
                      'class' => array('column-subtitle'),
                    ),
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );

    if (empty($values['subtitle']['value'])) {
      unset($content['wrapper']['row']['inner']['row']['col']['block']['subtitle']);
    }

    return $content;
  }

  public function preview($file = '') {
    $file = drupal_get_path('module', 'aaa_dynoblock_widgets') . '/themes/aaa/page_title/title.png';
    return parent::preview($file);
  }
}
