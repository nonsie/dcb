<?php
/**
 * @file
 * Contains DCBComponentManager.
 */

namespace Drupal\dcb;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Dynoblocks plugin manager.
 */
class DCBComponentManager extends DefaultPluginManager {

  /**
   * Constructs a DCBComponentManager object.
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
    parent::__construct('Plugin/DCBComponent', $namespaces, $module_handler, 'Drupal\dcb\DCBComponentInterface', 'Drupal\dcb\Annotation\DCBComponent');

    $this->alterInfo('dcb_info');
    $this->setCacheBackend($cache_backend, 'dcb');
  }
}
