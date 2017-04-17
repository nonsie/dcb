<?php

namespace Drupal\dcb\Plugin\DCBComponent\ExampleComponent;

use Drupal\dcb\Form\ComponentWizardBaseForm;
use Drupal\dcb\Plugin\DCBComponent\DCBComponentBase;

/**
 * Provides an 'Example' DCB Component.
 *
 * @DCBComponent(
 *   id = "ExampleComponent",
 *   name = @Translation("Example"),
 *   description_short = "Example Component for developers",
 *   default_theme = "dcb-example-default",
 *   themes = {
 *     "dcb-example-default" = {
 *       "label" = "Default",
 *       "template_dir" = "src/Plugin/DCBComponent/ExampleComponent",
 *       "handler" = "ExampleDefaultTheme",
 *       "preview_image" = "example.png",
 *     },
 *     "dcb-example-another" = {
 *       "label" = "Another",
 *       "template_dir" = "src/Plugin/DCBComponent/ExampleComponent",
 *       "handler" = "AnotherTheme",
 *       "preview_image" = "example.png",
 *     },
 *   },
 *   form_settings = {},
 *   properties = {
 *    "theme" = "dcb",
 *    "module" = "dcb",
 *    "preview_image" = "src/Plugin/DCBComponent/ExampleComponent/example.png",
 *   }
 * )
 */

class ExampleComponent extends DCBComponentBase {

  /**
   * @var \Drupal\dcb\Form\ComponentWizardBaseForm
   */
  public $componentform;

  public function init() {
    return $this;
  }

  public function adminForm(ComponentWizardBaseForm $componentWizardBaseForm, $values) {
    $this->componentform = $componentWizardBaseForm;
    $textarea_field = $this->getField('ckeditor_field', TRUE);

    $this->form = [
      'toplevel_example' => [
        '#type' => 'textfield',
        '#title' => 'Example top level element',
        '#default_value' => (isset($values['toplevel_example']) ? $values['toplevel_example'] : ''),
      ],
      'repeating_example' => [
        '#type' => 'dcb_repeating',
        '#more_button' => t('Add Another'),
        '#max_cardinality' => 6,
        '#initial_cardinality' => 3,
        '#default_value' => (isset($values['repeating_example']) ? $values['repeating_example'] : ''),
        'my_repeating_elements' => [
          'textarea_dcbfield_example' => $textarea_field->form([
            "#title" => 'example textarea dcbfield',
          ]),
          'basic_inner_example' => [
            '#type' => 'textfield',
            '#title' => 'basic inner example',
          ],
        ],
      ],
    ];

  }

  public function preRender($values) {
    $this->form_state = $values;
    $theme = !empty($this->themes[$values['theme']]['handler']) ? $this->themes[$values['theme']]['handler'] : NULL;
    if ($theme = $this->loadTheme($theme)) {
      $this->output = $theme->display($values);
    }
    return $this->output;
  }

}

