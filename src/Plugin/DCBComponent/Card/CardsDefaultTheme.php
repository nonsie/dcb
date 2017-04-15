<?php

namespace Drupal\dcb\Plugin\DCBComponent\Card;

use Drupal\dcb\DCBComponentTheme;

/**
 * Class AAACardsDefaultTheme.
 *
 * @package Drupal\dynoblock\Plugin\Dynoblock\Card
 */
class CardsDefaultTheme extends DCBComponentTheme {

  public function form(&$widget_form, $settings = array()) {}

  public function display($values = array(), $settings = array()) {
    $count = count($this->form_state[$this->form_state['widget']]);
    if ($count >= 3) {
      $column_count = 'three-columns';
    }
    elseif ($count == 2) {
      $column_count = 'two-columns';
    }
    else {
      $column_count = 'one-column';
    }
    $content = array(
      'columns' => array(
        '#type' => 'container',
        '#attributes' => array(
          'class' => array(
            'columns-container-outer',
            'container-fluid',
            'AAACardsDefaultTheme',
          ),
        ),
        'row' => array(
          '#type' => 'container',
          '#attributes' => array(
            'class' => array( "row" ),
          ),
          'inner' => array(
            '#type' => 'container',
            '#attributes' => array(
              'class' => array(
                'columns-container-inner',
                'container',
                'icons',
                $column_count,
              ),
            ),
            'row' => array(
              '#type' => 'container',
              '#attributes' => array(
                'class' => array( "row" ),
              ),
            ),
          ),
        ),
      ),
    );

    if (isset($values['BackgroundColor']['value'])) {
      $content['columns']['#attributes']['class'][] = $values['BackgroundColor']['value'];
    }

    if ($count >= 3) {
      $column_classes = array('column', 'col-sm-4', 'col-xs-12');
    }
    elseif ($count == 2) {
      $column_classes = array('column', 'col-sm-6', 'col-xs-12');
    }
    else {
      $column_classes = array('column', 'col-sm-push-4', 'col-sm-4', 'col-xs-12');
    }
    $content['columns']['row']['inner']['row']['inner'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array($column_count . "-container"),
      ),
    );
    $column = &$content['columns']['row']['inner']['row']['inner'];
    if (!empty($this->form_state[$this->form_state['widget']])) {
      foreach ($this->form_state[$this->form_state['widget']] as $delta => $value) {
        $items = $value['widget']['items'];
        $theme_settings = (isset($items['theme_settings']) ? $items['theme_settings'] : array());
        //$theme = new $items['theme']($this->form_state);
        $column['column'][$delta] = array(
          '#type' => 'container',
          '#attributes' => array(
            'class' => $column_classes,
          ),
        );
        if (isset($value['id'])) {
          $column['column'][$delta]['#attributes']['data-dyno-item-id'] = $value['id'];
        }
        //$column['column'][$delta][] = $theme->display($items, $theme_settings);
      }
    }
    if (isset($values['BorderControl']['value']['show-vertical-borders'])) {
      $content['columns']['row']['inner']['row']['inner']['#attributes']['class'][] = $values['BorderControl']['value']['show-vertical-borders'];
    }
    if (isset($values['BorderControl']['value']['show-horizontal-borders'])) {
      $content['columns']['row']['inner']['#attributes']['class'][] = $values['BorderControl']['value']['show-horizontal-borders'];
    }
    return $content;

  }

  public function preview($file = '') {
    if(empty($file)) {
      return parent::preview($this->plugin->themes['dcb-cards-default']['preview_image']);
    } else {
      return parent::preview($file);
    }
  }

}
