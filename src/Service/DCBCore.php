<?php

/**
 * @File: DCB core service.
 */

namespace Drupal\dcb\Service;

use Drupal\Core\Cache\CacheTagsInvalidator;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dcb\Generator\DCBComponentAdminFormGenerator;
use Drupal\dcb\Generator\DCBComponentStorageArrayGenerator;
use Drupal\dcb\Manager\DCBComponentManager;
use Drupal\dcb\PreRenderer\DCBPhpPreRenderer;
use Drupal\dcb\Renderer\DCBRendererFactory;

/**
 * Class DCBCore.
 *
 * @package Drupal\dynoblock\Service
 */
class DCBCore {

  /**
   * DCB Plugin Manager.
   *
   * @var \Drupal\dcb\Manager\DCBComponentManager.
   */
  private $pluginManager;

  /**
   * DCB DB Service.
   *
   * @var \Drupal\dcb\Service\DCBDb.
   */
  private $db;

  /**
   * Core Cache Tag Invalidator
   *
   * @var CacheTagsInvalidator
   */
  public $cacheTagsInvalidator;

  /**
   * @var \Drupal\dcb\Generator\DCBComponentAdminFormGenerator
   */
  private $adminFormGenerator;

  /**
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  private $moduleHandler;

  /**
   * @var \Drupal\dcb\Generator\DCBComponentStorageArrayGenerator
   */
  private $storageArrayGenerator;

  /**
   * @var \Drupal\dcb\PreRenderer\DCBPhpPreRenderer
   */
  private $preRenderer;

  /**
   * @var \Drupal\dcb\Renderer\DCBRendererFactory
   */
  private $rendererFactory;

  /**
   * DCBCore constructor.
   *
   * @param DCBComponentManager $pluginManager
   *   Injected.
   * @param DCBDb $dcbDb
   *   Injected.
   * @param \Drupal\Core\Extension\ModuleHandler $moduleHandler
   * @param \Drupal\Core\Cache\CacheTagsInvalidator $cacheTagsInvalidator
   * @param \Drupal\dcb\Generator\DCBComponentAdminFormGenerator $adminFormGenerator
   * @param \Drupal\dcb\Generator\DCBComponentStorageArrayGenerator $storageArrayGenerator
   * @param \Drupal\dcb\PreRenderer\DCBPhpPreRenderer $preRenderer
   * @param \Drupal\dcb\Renderer\DCBRendererFactory $rendererFactory
   */
  public function __construct(DCBComponentManager $pluginManager,
                              DCBDb $dcbDb,
                              ModuleHandler $moduleHandler,
                              CacheTagsInvalidator $cacheTagsInvalidator,
                              DCBComponentAdminFormGenerator $adminFormGenerator,
                              DCBComponentStorageArrayGenerator $storageArrayGenerator,
                              DCBPhpPreRenderer $preRenderer,
                              DCBRendererFactory $rendererFactory) {
    $this->pluginManager = $pluginManager;
    $this->db = $dcbDb;
    $this->moduleHandler = $moduleHandler;
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
    $this->adminFormGenerator = $adminFormGenerator;
    $this->storageArrayGenerator = $storageArrayGenerator;
    $this->preRenderer = $preRenderer;
    $this->rendererFactory = $rendererFactory;
  }

  /**
   * @param $regionId
   * @param $componentId
   * @param $entityId
   * @param $componentType
   *
   * @return array
   */
  public function getComponentAdminForm($regionId, $componentId, $entityId, $componentType) {
    $data = $this->db->getBlock($regionId, $componentId, '1');
    if ($componentType === '') {
      $componentType = $data['data']['meta']['component'];
    }
    $component = $this->getComponentInstance($componentType);
    $component->setInstanceData($data);
    $adminform = $this->adminFormGenerator->generate($regionId, $componentId, $entityId, $component);
    return $adminform;
  }

  /**
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *
   * @param $component_type
   *
   * @return array
   */
  public function saveComponentStorageArray(FormStateInterface $formState, $component_type) {
    $component = $this->getComponentInstance($component_type);
    $storageArray = $this->storageArrayGenerator->generate($formState, $component);
    $this->upsert($storageArray['rid'], $storageArray['bid'], $storageArray['revision'], $storageArray['data'], $storageArray['weight'], $storageArray['status']);
    return $storageArray;
  }

  /**
   * @param $rid
   * @param $entity
   * @param $region_label
   * @param $renderer_name
   *
   * @return mixed
   */
  public function renderRegion($rid, $entity, $region_label, $renderer_name) {
    $renderer = $this->rendererFactory->getRenderer($renderer_name);
    $region_data = $this->db->getBlocks($rid, '1');
    $prerenderdata = [];
    foreach($region_data as $component_data) {
      $prerenderdata[] = $this->preRenderComponent($component_data);
    }
    return $renderer->renderRegion($rid, $entity, $region_label, $prerenderdata);
  }

  /**
   * @param $rid
   * @param $bid
   * @param $renderer_name
   *
   * @return mixed
   */
  public function renderComponent($rid, $bid, $revision, $renderer_name) {
    $renderer = $this->rendererFactory->getRenderer($renderer_name);
    $region_data = $this->db->getBlocks($rid, $revision);
    foreach($region_data as $component_data) {
      if ($component_data['bid'] === $bid) {
        $prerenderdata = $this->preRenderComponent($component_data);
        return $renderer->renderComponent($prerenderdata);
      }
    }
    return [];
  }

  /**
   * @param $component_data
   *
   * @return mixed
   */
  public function preRenderComponent($component_data) {
    $component = $this->getComponentInstance($component_data['data']['meta']['component']);
    $component->setInstanceData($component_data);
    return $this->preRenderer->preRender($component);
  }

  /**
   * @param $componentType
   *
   * @return \Drupal\dcb\Base\Component\DCBComponentInterface
   */
  public function getComponentInstance($componentType) {
    /** @var \Drupal\dcb\Base\Component\DCBComponentInterface $componentInstance */
    $componentInstance = $this->pluginManager->createInstance($componentType);
    return $componentInstance;
  }

  /**
   * @return array|\mixed[]|null
   */
  public function getComponentList() {
    return $this->pluginManager->getDefinitions();
  }

  /**
   * @param $rid
   * @param $bid
   * @param string $revision
   * @param null $data
   * @param null $weight
   * @param string $status
   */
  protected function upsert($rid, $bid, $revision = '1', $data = NULL, $weight = NULL, $status = NULL) {
    if ($data == NULL || $weight == NULL || $status == NULL) {
      $dbdata = $this->db->getBlock($rid, $bid, $revision);
      $data = $data !== NULL ? $data : $dbdata['data'];
      $weight = $weight !== NULL ? $weight : $dbdata['weight'];
      $status = $status !== NULL ? $status : $dbdata['status'];
    }
    $record = [
      'rid' => $rid,
      'bid' => $bid,
      'revisionid' => $revision,
      'data' => $data,
      'weight' => $weight,
      'status' => $status,
    ];
    $this->db->save($record);
  }

  /**
   * @param $rid
   * @param $bid
   *
   * @return mixed
   */
  public function updateWeight($rid, $bid, $revision, $weight) {
    $result = FALSE;
    $this->upsert($rid, $bid, $revision, NULL, $weight, NULL);
    return ['result' => TRUE];
  }

  /**
   * @param $rid
   * @param $bid
   * @return array
   */
  public function removeBlock($rid, $bid) {
    $removed = $this->db->remove($rid, $bid);
    return ['removed' => $removed];
  }

  /**
   * Invalidates cache for a specific entity ID.
   *
   * @param $entity_type
   * @param $entity_id
   * @return string
   */
  public function invalidateCache($entity_type, $entity_id) {
    if (!empty($entity_type) && !empty($entity_id)) {
      $cache_tag = $entity_type . ':' . $entity_id;
      $this->cacheTagsInvalidator->invalidateTags([$cache_tag]);
      return "Success";
    }
    else {
      return "Failure";
    }
  }

}
