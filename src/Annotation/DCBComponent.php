<?php

/**
 * @file
 * Contains \Drupal\dynoblock\Annotation\Flavor.
 */

namespace Drupal\dcb\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a flavor item annotation object.
 *
 * Plugin Namespace: Plugin\dcb\DCBComponent
 *
 * @see \Drupal\dcb\DCBComponentManager
 * @see plugin_api
 *
 * @Annotation
 */
class DCBComponent extends Plugin {

  /**
   * The plugin Widget ID.
   *
   * @var string
   */
  public $id;

  /**
   * The name of the Plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $name;

  /**
   * Widgets optional themes.
   *
   * @var array
   *   e.g array('grey' => array('template' => 'templates/grey.html.twig'))
   */
  public $themes;

  /**
   * The default theme.
   *
   * @var string
   */
  public $default_theme;

  /**
   * @var string.
   */
  public $description;

  /**
   * @var string.
   */
  public $description_short;

  /**
   * @var array.
   */
  public $properties;

  /**
   * Form settings.
   *
   * @var array
   *   e.g array('cardinality' => -1, 'variant_support' => TRUE)
   */
  public $form_settings;

}
