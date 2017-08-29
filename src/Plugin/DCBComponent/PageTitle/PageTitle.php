<?php

/**
 * @File: Defines the page title Component.
 */

namespace Drupal\dcb\Plugin\DCBComponent\PageTitle;

use Drupal\dcb\Plugin\DCBComponent\DCBComponentBase;

/**
 * Provides a 'Page Title' DCB Component.
 *
 * @DCBComponent(
 *   id = "PageTitle",
 *   name = @Translation("Page Title"),
 *   description_short = "Page title widget",
 *   defaultTheme = "dcb-page-title-default",
 *   themes = {
 *     "dcb-page-title-default" = {
 *        "label" = "Default",
 *        "template_dir" = "src/Plugin/DCBComponent/PageTitle",
 *        "handler" = "PageTitleDefaultTheme",
 *        "previewImage" = "title.png",
 *     },
 *     "dcb-page-title-gray" = {
 *        "label" = "Gray",
 *        "template_dir" = "src/Plugin/DCBComponent/PageTitle",
 *        "handler" = "PageTitleGrayTheme",
 *        "previewImage" = "one_col.png",
 *     }
 *   },
 *   formSettings = {
 *     "variant_support" = 1,
 *   },
 *   properties = {
 *    "theme" = "dcb",
 *    "module" = "dcb",
 *    "previewImage" = "src/Plugin/DCBComponent/PageTitle/title.png",
 *   }
 * )
 */
class PageTitle extends DCBComponentBase {

  /**
   * @param $values
   * @return mixed
   */
  public function outerForm($values) {

    $myform['title'] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' => !empty($values['title']) ? $values['title'] : NULL,
    ];

    $select_field = $this->getField('select_field', TRUE, $values);

    $myform['tag'] = $select_field->form(
      [
        "#title" => t('HTML tag'),
        '#default_value' => !empty($values['tag']) ? $values['tag'] : 'h2',
        '#options' => [
          'h1' => 'h1',
          'h2' => 'h2',
          'h3' => 'h3',
          'h4' => 'h4',
        ],
      ]
    );

    $myform['class_name'] = [
      '#type' => 'textfield',
      '#title' => t('Optional class(es)'),
      '#description' => t('One or more classes to apply to the title tag'),
      '#default_value' => !empty($values['class_name']) ? $values['class_name'] : '',
    ];

    return $myform;

  }

}
