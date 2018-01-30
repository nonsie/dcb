<?php

/**
 * @file
 * Contains dcb_component.page.inc.
 *
 * Page callback for DCB Component entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for DCB Component templates.
 *
 * Default template: dcb_component.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_dcb_component(array &$variables) {
  // Fetch DCBComponent Entity Object.
  $dcb_component = $variables['elements']['#dcb_component'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
