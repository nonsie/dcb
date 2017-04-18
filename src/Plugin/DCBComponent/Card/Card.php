<?php

/**
 * @File: Defines the Card component plugin.
 */

namespace Drupal\dcb\Plugin\DCBComponent\Card;


use Drupal\dcb\Form\ComponentWizardBaseForm;
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
   * @var \Drupal\dcb\Form\ComponentWizardBaseForm
   */
  public $componentform;


  /**
   * @return $this
   */
  public function init() {
    return $this;
  }

  /**
   * @param \Drupal\dcb\Form\ComponentWizardBaseForm $componentform
   * @param array $values
   * @return $this
   */
  public function build(ComponentWizardBaseForm $componentform, array $values) {
    // sets the $form_state that may or may not be used in other places.
    $this->componentform = $componentform;

    $this->form['fields'] = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#attributes' => [
        'class' => [''],
        'id' => 'card-fields',
      ],
    ];

    return $this;
  }

  /**
   * @param array $form_state
   * @param $items
   * @param $delta
   * @return mixed
   */
  public function widgetForm(&$form_state = [], $items, $delta) {
    $container_id = $this->componentform->randId();
    $element['items'] = [
        '#type' => 'details',
        '#title' => t('Item @delta', [
          '@delta' => ($delta + 1),
        ]),
        '#open' => $this->getWidgetDetailsState($form_state),
        '#collapsible' => TRUE,
        '#attributes' => [
          'id' => $container_id,
        ],
      ] + $this->addFields(!empty($items[$delta]) ? $items[$delta] : [], $delta, $container_id);
    return $element;
  }


  /**
   * @param array $values
   * @param $delta
   * @param $container_id
   * @return mixed
   */
  private function addFields($values = [], $delta, $container_id) {
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


  /**
   * @param $values
   * @return mixed
   */
  public function preRender($values) {
    $this->form_state = $values;
    $theme = !empty($this->themes[$values['theme']]['handler']) ? $this->themes[$values['theme']]['handler'] : NULL;
    if ($theme = $this->loadTheme($theme)) {
      $this->layout = $theme->display($values);
    }
    return $this->layout;
  }

}
