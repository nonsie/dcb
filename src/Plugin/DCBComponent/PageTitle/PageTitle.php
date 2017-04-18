<?php

/**
 * @File: Defines the page title Component.
 */

namespace Drupal\dcb\Plugin\DCBComponent\PageTitle;

use Drupal\dcb\Form\ComponentWizardBaseForm;
use Drupal\dcb\Plugin\DCBComponent\DCBComponentBase;

/**
 * Provides a 'Page Title' DCB Component.
 *
 * @DCBComponent(
 *   id = "PageTitle",
 *   name = @Translation("Page Title"),
 *   description_short = "Page title widget",
 *   default_theme = "dcb-page-title-default",
 *   themes = {
 *     "dcb-page-title-default" = {
 *        "label" = "Default",
 *        "template_dir" = "src/Plugin/DCBComponent/PageTitle",
 *        "handler" = "PageTitleDefaultTheme",
 *        "preview_image" = "title.png",
 *     },
 *     "dcb-page-title-gray" = {
 *        "label" = "Gray",
 *        "template_dir" = "src/Plugin/DCBComponent/PageTitle",
 *        "handler" = "PageTitleGrayTheme",
 *        "preview_image" = "one_col.png",
 *     }
 *   },
 *   form_settings = {
 *     "variant_support" = 1,
 *   },
 *   properties = {
 *    "theme" = "dcb",
 *    "module" = "dcb",
 *    "preview_image" = "src/Plugin/DCBComponent/PageTitle/title.png",
 *   }
 * )
 */
class PageTitle extends DCBComponentBase {

  /**
   * @return $this
   */
  public function init() {
    return $this;
  }

  /**
   * @param \Drupal\dcb\Form\ComponentWizardBaseForm $componentform
   * @param array $values
   * @return float|void
   */
  public function build(ComponentWizardBaseForm $componentform, array $values) {

    $this->form['fields']['#tree'] = TRUE;
    $this->form['fields']['title'] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' => !empty($values['fields']['title']) ? $values['fields']['title'] : NULL,
    ];

    $select_field = $this->getField('select_field', TRUE, $values);
    $this->form['fields']['tag'] = $select_field->form(
      [
        "#title" => t('HTML tag'),
        '#default_value' => !empty($values['fields']['tag']) ?
          $values['fields']['tag'] : 'h2',
        '#options' => [
          'h1' => 'h1',
          'h2' => 'h2',
          'h3' => 'h3',
          'h4' => 'h4',
        ],
      ]
    );

    $this->form['fields']['class_name'] = [
      '#type' => 'textfield',
      '#title' => t('Optional class(es)'),
      '#description' => t('One or more classes to apply to the title tag'),
      '#default_value' => !empty($values ['fields']['class_name']) ?
        $values ['fields']['class_name'] : '',
    ];

  }

  /**
   * @param $values
   * @return mixed
   */
  public function preRender($values) {
    $this->form_state = $values;
    $theme = !empty($this->themes[$values['theme']]['handler']) ? $this->themes[$values['theme']]['handler'] : NULL;
    if ($theme = $this->loadTheme($theme)) {
      $this->output = $theme->display($values);
    }
    return $this->output;
  }

}
