<?php
/**
 * Created by PhpStorm.
 * User: garymorse
 * Date: 3/24/17
 * Time: 12:52 PM
 */

namespace Drupal\dynoblock\Annotation;


use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Dynoblocks field annotation object.
 *
 * Plugin Namespace: Plugin\dynoblock\DynoField
 *
 * @see \Drupal\dynoblock\DynoFieldManager
 * @see plugin_api
 *
 * @Annotation
 */
class DynoField extends Plugin {

  /**
   * The plugin dynofield ID.
   *
   * @var string
   */
  public $id;

  /**
   * The name of the dynofield.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $name;

}