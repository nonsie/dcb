<?php

namespace Drupal\dynoblock\Plugin\Dynoblock\PageTitle;

use Drupal\dynoblock\Plugin\Dynoblock\DynoblockBase;
use Drupal\dynoblock\DynoBlockForms;
use Drupal\dynoblock\DynoWidgetAPI;

/**
 * Provides a 'page Title' Dynoblock Widget.
 *
 * @Dynoblock(
 *   id = "PageTitle",
 *   name = @Translation("Page Title"),
 *   description_short = "Page title widget",
 *   default_theme = "dynoblock-page-title-default",
 *   themes = {
 *     "dynoblock-page-title-default" = {
 *        "label" = "Default",
 *        "template_dir" = "src/Plugin/Dynoblock/PageTitle",
 *        "handler" = "PageTitleDefaultTheme",
 *        "preview_image" = "title.png",
 *     },
 *     "dynoblock-page-title-gray" = {
 *        "label" = "Gray",
 *        "template_dir" = "src/Plugin/Dynoblock/PageTitle",
 *        "handler" = "PageTitleGrayTheme",
 *        "preview_image" = "one_col.png",
 *     }
 *   },
 *   form_settings = {
 *     "variant_support" = 1,
 *     "cardinality" = -1,
 *   },
 *   properties = {
 *    "theme" = "dynoblock",
 *    "module" = "dynoblock",
 *    "preview_image" = "src/Plugin/Dynoblock/PageTitle/title.png",
 *   }
 * )
 */
class PageTitle extends DynoblockBase {

  public function init() {
    // TODO: ....
    return $this;
  }

  public function build($form_state = array()) {
    $form_state = is_object($form_state) ? (array) $form_state : $form_state;
    $this->form['fields']['#tree'] = TRUE;

    $this->form['fields']['title'] = array(
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' => !empty($form_state['fields']['title']) ? $form_state['fields']['title'] : NULL,
    );

    $text_field = $this->getField('text_field', TRUE, $form_state);
    $this->form['fields']['anotherfield'] = $text_field->form(
      array(
        "#title" => 'testing field title',
        '#default_value' => !empty($form_state['fields']['anotherfield']['value']) ?  $form_state['fields']['anotherfield']['value'] : '',
      )
    );

    $textarea_field = $this->getField('textarea_field', TRUE, $form_state);
    $this->form['fields']['textarea'] = $textarea_field->form(
      array(
        "#title" => 'testing field title',
        '#default_value' => !empty($form_state['fields']['textarea']['value']) ?  $form_state['fields']['textarea']['value'] : '',
      )
    );

    /*$ckeditor_field = $this->getField('ckeditor_field', TRUE, $form_state);
    $this->form['fields']['ckeditor'] = $ckeditor_field->form(
      array(
        "#title" => 'testing field title',
        '#default_value' => !empty($form_state['fields']['ckeditor']['value']['value']) ?  $form_state['fields']['ckeditor']['value']['value'] : '',
      )
    );*/

  }

  public function preRender($values) {
    $this->form_state = $values;
    $theme = !empty($this->themes[$values['theme']]['handler']) ? $this->themes[$values['theme']]['handler'] : NULL;
    if ($theme = $this->loadTheme($theme)) {
      $this->output = $theme->display($values);
    }
    return $this->output;
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
