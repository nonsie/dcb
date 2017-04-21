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
   * @description: The outerForm method is used to define form elements that
   * appear only once on a component. $values contains the stored previous values
   * of the form and should be used to populate #default_value
   *
   * return a standard form render array.
   */
  public function outerForm($values) {

    $myform = [
      'textfield' => [
        '#type' => 'textfield',
        '#title' => t('Outer Field'),
        '#default_value' => isset($values['textfield']) ? $values['textfield'] : '',
      ],
    ];

    /**
     * This is an example of creating an optional field. When implementing
     * fieldOptions, pass the array shown below along with the other arguments
     * to invoke an options DCBField of type 'plugin. text_field optional
     * field is shown below.
     */
    $this->registerOuterFieldOptions([
      [
        'plugin' => 'text_field',
        'field_name' => 'option-text-field',
        'label' => t('field'),
        'properties' => [
          '#title' => t('text'),
          '#default_value' => !empty($values['option-text-field']['value']) ? $values['option-text-field']['value'] : '',
        ],
      ],
    ]);

    return $myform;
  }

  /**
   * @param array $values
   * @param $delta
   * @param $container_id
   * @return mixed
   *
   * @description: The repeatingFields method is used to define a set of fields
   * that repeat. These fields will be wrapped in collapsible details elements
   * and an "add another" button will be added. $values contains the stored values
   * for the specific item being rendered and this method is called repeatedly
   * to reach the necessary cardinality (cardinality is set in the plugin annotation
   * above).
   *
   * In addition to repeating elements, these items can have their own themes and
   * optional fields. The use of registerItemThemeOptions and
   * registerInnerFieldOptions below are examples of how to implement these options for a item.
   *
   */
  public function repeatingFields($values = [], $delta) {
    // Shorten the $values array to the necessary items.
    $values = isset($values['widget']['items']) ? $values['widget']['items'] : [];

    /**
     * This is an example of retrieving a DCBField. This ckeditor_field can be
     * reused on many components.
     */
    $textarea_field = $this->getField('ckeditor_field', TRUE);
    $item['body'] = $textarea_field->form([
      "#title" => 'testing field title',
      '#default_value' => !empty($values['body']['value']['value']) ? $values['body']['value']['value'] : '',
    ]);

    /**
     * This is an example of adding themes to an item. This allows for each item to
     * look and act different. Each 'themes' below should be created as a class implementing
     * \DCBComponentTheme.
     * @see \Drupal\dcb\Plugin\DCBComponent\Card\CardsDefaultTheme
     * @see \Drupal\dcb\Plugin\DCBComponent\Card\AAACardTextCenterItemTheme
     */
    $this->registerItemThemeOptions([
      $delta => [
        'themes' => [
          'AAACardDefaultItemTheme' => t('Default (text align left)'),
          'AAACardTextCenterItemTheme' => t('Center (text align center)'),
        ],
        'default' => 'AAACardDefaultItemTheme',
      ]
    ]);

    /**
     * This is an example of creating an optional field. This can also be used in
     * outerForm above. When implementing fieldOptions, pass the array shown below
     * along with the other arguments to invoke an options DCBField of type 'plugin.
     * text_field optional field is shown below.
     * @see \Drupal\dcb\Form\ComponentWizardBaseForm::fieldOptions()
     */
    $this->registerInnerFieldOptions([
      $delta => [
        [
          'plugin' => 'text_field',
          'field_name' => 'test',
          'label' => t('Textfield'),
          'properties' => [
            '#title' => t('Textfield'),
            '#default_value' => !empty($values['test']['value']) ? $values['test']['value'] : '',
          ],
        ],
      ],
    ]);

    return $item;
  }

}
