<?php

namespace Drupal\dcb_extras\Plugin\DCBField\Link;

use Drupal\dcb\Plugin\DCBField\DCBFieldBase;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;

/**
 * Provides a 'Link' DCBField Widget.
 *
 * @DCBField(
 *   id = "link",
 *   name = @Translation("Link"),
 * )
 */
class Link extends DCBFieldBase {

  /**
   * @param array $properties
   * @return mixed
   */
  public function form( array $properties = []) {
    $field['copy'] = [
      '#type' => 'textfield',
      '#title' => t('Link Copy'),
      '#default_value' => !empty($properties['#default_value']['copy']) ? $properties['#default_value']['copy'] : '',
    ];
    $field['path'] = [
      '#type' => 'textfield',
      '#title' => t('Link Path'),
      '#maxlength' => 512,
      '#default_value' => !empty($properties['#default_value']['path']) ? $properties['#default_value']['path'] : '',
    ];
    $field['target'] = [
      '#title' => t('Open Link In'),
      '#type' => 'select',
      '#options' => [
        '_self' => t('Default (Same window)'),
        '_blank' => t('New window'),
        'modal' => t('Modal window'),
      ],
      '#default_value' => !empty($properties['#default_value']['target']) ? $properties['#default_value']['target'] : '',
    ];

    $this->setFormElement($field);
    $this->field['value'] += [
      '#type' => 'fieldset',
      '#title' => t('Link'),
    ];
    return $this->field;
  }

  /**
   * @param $value
   * @param $bid
   */
  public function onSubmit($value, $bid) {}

  /**
   * @param $value
   * @param array $settings
   * @return array
   */
  public function render($value, $settings = []) {
    if (isset($value['path'])) {
      $node = \Drupal::request()->attributes->get('node');
      if (!empty($node)) {
        $token_service = \Drupal::service('token');
        $value['path'] = $token_service->replace($value['path'], ['node' => $node]);
      }
      $url_values = UrlHelper::parse($value['path']);
      $target = isset($value['target']) && $value['target'] != 'modal' ? $value['target'] : '';
      $classes[] = 'LinkField';
      if (isset($value['target']) && $value['target'] == 'modal') {
        $classes[] = 'modal-show';
      }
      $uri = Url::fromUri($url_values['path'], [
        'query' => $url_values['query'],
        'fragment' => $url_values['fragment'],
      ]);

      return $settings + [
        '#type' => 'html_tag',
        '#tag' => 'a',
        '#value' => $value['copy'],
        '#attributes' => [
          'class' => $classes,
          'href' => $uri->toString(),
          'target' => $target,
        ],
      ];
    }
  }

}
