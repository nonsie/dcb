<?php

/**
 * @File: Plugin Manager for DCBField plugins.
 */

namespace Drupal\dcb\Manager;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class DCBFieldManager
 * @package Drupal\dcb\Plugin\DCBField
 */
class DCBFieldManager extends DefaultPluginManager {

  /**
   * Constructs a DCBFieldManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations,
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */

  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/DCBField', $namespaces, $module_handler, 'Drupal\dcb\Base\Field\DCBFieldInterface', 'Drupal\dcb\Annotation\DCBField');

    $this->alterInfo('dcb_fieldInfo');
    $this->setCacheBackend($cache_backend, 'dcbfield');
  }

}
