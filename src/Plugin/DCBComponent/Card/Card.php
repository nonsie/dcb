<?php

/**
 * @File: Defines the Card component plugin.
 */

namespace Drupal\dcb\Plugin\DCBComponent\Card;

use Drupal\dcb\Plugin\DCBComponent\DCBComponentBase;


/**
 * Provides a 'Card' DCB Component.
 *
 * @DCBComponent(
 *   id = "Card",
 *   name = @Translation("Card"),
 *   description_short = "A card is a flexible and extensible content container. It includes options for headers and footers, a wide variety of content, contextual background colors, and powerful display options.",
 *   default_theme = "dcb-cards-default",
 *   themes = {
 *     "dcb-cards-default" = {
 *        "label" = "Default",
 *        "handler" = "CardsDefaultTheme",
 *        "preview_image" = "cards.png",
 *     },
 *   },
 *   form_settings = {
 *     "variant_support" = 1,
 *     "cardinality" = -1,
 *   },
 *   properties = {
 *    "theme" = "dcb",
 *    "module" = "dcb",
 *    "preview_image" = "src/Plugin/DCBComponent/Card/cards.png",
 *   }
 * )
 */
class Card extends DCBComponentBase {


  /**
   * @param $values
   * @return mixed
   */
  public function outerForm($values) {

    $myform = [
      'textfield' => [
        '#type' => 'textfield',
        '#title' => t('Outer Field'),
        '#default_value' => isset($values['textfield']) ? $values['textfield'] : '',
      ],
    ];

    return $myform;
  }

  /**
   * @param array $values
   * @param $delta
   * @param $container_id
   * @return mixed
   */
  public function repeatingFields($values = [], $delta, $container_id) {
    $values = isset($values['widget']['items']) ? $values['widget']['items'] : [];
    $textarea_field = $this->getField('ckeditor_field', TRUE);
    $item['body'] = $textarea_field->form([
      "#title" => 'testing field title',
      '#default_value' => !empty($values['body']['value']['value']) ? $values['body']['value']['value'] : '',
    ]);
    $this->componentform->themeOptions($this, $item, $delta, $values, $container_id, [
      'themes' => [
        'AAACardDefaultItemTheme' => t('Default (text align left)'),
        'AAACardTextCenterItemTheme' => t('Center (text align center)'),
      ],
      'default' => 'AAACardDefaultItemTheme',
    ]);
    $this->componentform->fieldOptions($this, $item, $values, $container_id, [
      [
        'plugin' => 'text_field',
        'field_name' => 'test',
        'label' => t('Textfield'),
        'properties' => [
          '#title' => t('Textfield'),
          '#default_value' => !empty($values['test']['value']) ? $values['test']['value'] : '',
        ],
      ],
    ], $delta);

    return $item;
  }

}
