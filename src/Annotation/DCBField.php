<?php

namespace Drupal\dcb\Annotation;


use Drupal\Component\Annotation\Plugin;

/**
 * Defines a DCB field annotation object.
 *
 * Plugin Namespace: Plugin\dcb\DCBField
 *
 * @see \Drupal\dcb\Plugin\DCBField\DCBFieldManager
 * @see plugin_api
 *
 * @Annotation
 */
class DCBField extends Plugin {

  /**
   * The plugin dynofield ID.
   *
   * @var string
   */
  public $id;

  /**
   * The name of the DCBField.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $name;

}