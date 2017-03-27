<?php

namespace Drupal\dynoblock\Plugin\Dynoblock\PageTitle;

use Drupal\dynoblock\DynoblockBase;
use Drupal\dynoblock\DynoBlockForms;
use Drupal\dynoblock\DynoWidgetAPI;

/**
 * Provides a 'page Title' Dynoblock Widget.
 *
 * @Dynoblock(
 *   id = "page_title",
 *   name = @Translation("Page Title"),
 *   description_short = "Page title widget",
 *   default_theme = "default",
 *   form_settings = {
 *     "variant_support" = 1,
 *   },
 *   properties = {
 *    "theme" = "dynoblock",
 *    "module" = "dynoblock",
 *    "preview_image" = "src/Plugin/Dynoblock/PageTitle/title.png",
 *   }
 * )
 */
class PageTitle extends DynoblockBase {

  public $form;
  public $form_state;

  public function init() {
    // TODO: ....
    return $this;
  }

  public function build($form_state = array()) {
    $this->form['fields']['title'] = array(
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' => !empty($form_state['title']) ? $form_state['title'] : NULL,
    );
  }

  public function preRender($values) {
    return $this->layout = array(
      '#type' => 'markup',
      '#markup' => '<h2>'. $values['title'] .'</h2>',
    );
  }

  /**
   * {@inheritdoc}
   */
//  public function widgetForm(&$form_state = array(), $items, $delta) {
//    $collapsed = TRUE;
//    $container_id = DynoBlockForms::randId();
//    if (isset($form_state['triggering_element']['#attributes']['delta'])) {
//      $trigger_delta = $form_state['triggering_element']['#attributes']['delta'];
//      if ($trigger_delta == $delta) $collapsed = FALSE;
//    }
//    $element['items'] = array(
//      '#type' => 'fieldset',
//      '#title' => t('Item @delta', array(
//        '@delta' => ($delta + 1),
//      )),
//      '#collapsed' => $collapsed,
//      '#collapsible' => TRUE,
//      '#attributes' => array(
//        'id' => $container_id,
//      ),
//    ) + $this->addFields(!empty($items[$delta]) ? $items[$delta] : array() , $delta, $container_id);
//    return $element;
//  }
//
//  private function addFields($values = array(), $delta, $container_id) {
//    $values = !empty($values['widget']['items']) ? $values['widget']['items'] : array();
//    $item['title'] = array(
//      '#type' => 'textfield',
//      '#title' => t('Title'),
//      '#default_value' => !empty($values['title']['value']) ? $values['title']['value'] : '',
//    );
//    return $item;
//  }

}
