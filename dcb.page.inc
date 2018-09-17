<?php

/**
 * @file
 * Contains dcb.page.inc.
 *
 * Page callback for DCB entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for DCB templates.
 *
 * Default template: dcb.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_dcb(array &$variables) {
  // Fetch DCB Entity Object.
  $dcb = $variables['elements']['#dcb'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
