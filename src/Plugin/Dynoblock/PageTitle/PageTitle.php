<?php

namespace Drupal\dynoblock\Plugin\Dynoblock\PageTitle;

use Drupal\dynoblock\Form\ComponentWizardBaseForm;
use Drupal\dynoblock\Plugin\Dynoblock\DynoblockBase;

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

  public function build(ComponentWizardBaseForm $componentform, array $values) {

    $this->form['fields']['#tree'] = TRUE;
    $this->form['fields']['title'] = array(
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' => !empty($values['fields']['title']) ? $values['fields']['title'] : NULL,
    );

    $select_field = $this->getField('select_field', TRUE, $values );
    $this->form['fields']['tag'] = $select_field->form(
      array(
        "#title" => t('HTML tag'),
        '#default_value' => !empty($values['fields']['tag']) ?
          $values['fields']['tag'] : 'h2',
        '#options' => array(
          'h1' => 'h1',
          'h2' => 'h2',
          'h3' => 'h3',
          'h4' => 'h4',
        ),
      )
    );

    $this->form['fields']['class_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Optional class(es)'),
      '#description' => t('One or more classes to apply to the title tag'),
      '#default_value' => !empty($values ['fields']['class_name']) ?
        $values ['fields']['class_name'] : '',
    );

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
