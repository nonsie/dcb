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
    $content = [
      'columns' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'columns-container-outer',
            'container-fluid',
            'AAACardsDefaultTheme',
          ],
        ],
        'row' => [
          '#type' => 'container',
          '#attributes' => [
            'class' => ["row"],
          ],
          'inner' => [
            '#type' => 'container',
            '#attributes' => [
              'class' => [
                'columns-container-inner',
                'container',
                'icons',
                $column_count,
              ],
            ],
            'row' => [
              '#type' => 'container',
              '#attributes' => [
                'class' => ["row"],
              ],
            ],
          ],
        ],
      ],
    ];

    if (isset($values['BackgroundColor']['value'])) {
      $content['columns']['#attributes']['class'][] = $values['BackgroundColor']['value'];
    }

    if ($count >= 3) {
      $column_classes = ['column', 'col-sm-4', 'col-xs-12'];
    }
    elseif ($count == 2) {
      $column_classes = ['column', 'col-sm-6', 'col-xs-12'];
    }
    else {
      $column_classes = [
        'column',
        'col-sm-push-4',
        'col-sm-4',
        'col-xs-12'
      ];
    }
    $content['columns']['row']['inner']['row']['inner'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [$column_count . "-container"],
      ],
    ];
    $column = &$content['columns']['row']['inner']['row']['inner'];
    if (!empty($this->form_state[$this->form_state['widget']])) {
      foreach ($this->form_state[$this->form_state['widget']] as $delta => $value) {
        $items = $value['widget']['items'];
        $column['column'][$delta] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => $column_classes,
          ],
        ];
        if (isset($value['id'])) {
          $column['column'][$delta]['#attributes']['data-dyno-item-id'] = $value['id'];
        }

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

  /**
   * @param string $file
   * @return mixed|null
   */
  public function preview($file = '') {
    if (empty($file)) {
      return parent::preview($this->plugin->themes['dcb-cards-default']['preview_image']);
    }
    else {
      return parent::preview($file);
    }
  }

}
