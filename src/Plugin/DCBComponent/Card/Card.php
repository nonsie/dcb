<?php

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
 *   default_theme = "dynoblock-cards-default",
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


  public function init() {

    return $this;
  }

  /**
   * @param \Drupal\dcb\Form\ComponentWizardBaseForm $componentform
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
        'id'    => 'card-fields',
      ],
    ];

    return $this;
  }

  public function widgetForm(&$form_state = array(), $items, $delta) {
    $container_id = $this->componentform->randId();
    $element['items'] = array(
        '#type' => 'details',
        '#title' => t('Item @delta', array(
          '@delta' => ($delta + 1),
        )),
        '#open'  => $this->getWidgetDetailsState($form_state),
        '#collapsible' => TRUE,
        '#attributes' => array(
          'id' => $container_id,
        ),
      ) + $this->addFields(!empty($items[$delta]) ? $items[$delta] : array() , $delta, $container_id);
    return $element;
  }

  /**
   * Helper function to add a group of fields this widget uses..
   *
   *  - @see DynoWidgetAPI::element
   *    Using this allows for many great things:
   *      Allows for you to use pre defined fileds and thier displays that can be interganged between themes and widgets.
   *      These fields classes need to be added to your parent themes field.inc file.
   *      @see abstract class DCBField
   *
   *  - @see DynoBlockForms::themeOptions
   *    This allows you to add custom themes to your group of fields.
   *    These theme classes need to be added to your parent themes theme.inc file.
   *    @see abstract class DCBComponentTheme
   *
   *
   */
  private function addFields($values = array(), $delta, $container_id) {
//    $item['body'] = DynoWidgetAPI::element($this->form_state, 'AAACkeditorField', array(
//      '#title' => t('Body'),
//      '#default_value' => $body_default_value,
//    ));
//
//    $item['merchtag'] = DynoWidgetAPI::element($this->form_state, 'AAAColumnTag', array(
//      '#title' => t('Tag'),
//      '#default_value' => (!empty($values['merchtag']['value']) ? $values['merchtag']['value'] : ''),
//    ));
//
//    // This adds the ablitlity to have different sub-themes for each item group in this widget.
//    // Theme Classes need to go inside the widgets parent theme directory. e.g: aaa_dynoblock_widgets/themes/aaaa/theme.inc
//    DynoBlockForms::themeOptions($item, $delta, $values, array(
//      'themes' => array(
//        'AAACardDefaultItemTheme' => t('Default (text align left)'),
//        'AAACardTextCenterItemTheme' => t('Center (text align center)'),
//      ),
//      'default' => 'AAACardDefaultItemTheme',
//    ));
//    // This allows for extra fields to be added to to this widgets field group.
//    // Some widgets may be able to accept button or links in their layout. We only want those fields to be added when needed.
//    DynoBlockForms::fieldOptions($item, $values, $container_id, array(
//      array(
//        'handler' => 'AAAButtonField',
//        'field_name' => 'button',
//        'label' => t('AAA Button'),
//        'properties' => array(
//          '#default_value' => !empty($values['button']['value']) ? $values['button']['value'] : '',
//        ),
//      ),
//      array(
//        'handler' => 'AAALinkField',
//        'field_name' => 'link',
//        'label' => t('Link Field'),
//        'properties' => array(
//          '#default_value' => !empty($values['link']['value']) ? $values['link']['value'] : '',
//        ),
//      ),
//    ), $delta);
    $values = isset($values['widget']['items']) ? $values['widget']['items'] : array();
    $textarea_field = $this->getField('ckeditor_field', TRUE);
    $item['body'] = $textarea_field->form([
      "#title" => 'testing field title',
      '#default_value' => !empty($values['body']['value']['value']) ? $values['body']['value']['value'] : '',
    ]);
    $this->componentform->themeOptions($this, $item, $delta, $values, $container_id, array(
      'themes' => array(
        'AAACardDefaultItemTheme' => t('Default (text align left)'),
        'AAACardTextCenterItemTheme' => t('Center (text align center)'),
      ),
      'default' => 'AAACardDefaultItemTheme',
    ));
    $this->componentform->fieldOptions($this, $item, $values, $container_id, array(
      array(
        'plugin' => 'text_field',
        'field_name' => 'test',
        'label' => t('Textfield'),
        'properties' => array(
          '#title' => t('Textfield'),
          '#default_value' => !empty($values['test']['value']) ? $values['test']['value'] : '',
        ),
      ),
    ), $delta);
    return $item;
  }


  public function preRender($values) {
    $this->form_state = $values;
    $theme = !empty($this->themes[$values['theme']]['handler']) ? $this->themes[$values['theme']]['handler'] : NULL;
    if ($theme = $this->loadTheme($theme)) {
      $this->layout = $theme->display($values);
    }
    return $this->layout;
  }

}
