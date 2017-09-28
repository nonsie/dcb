<?php

namespace Drupal\dcb\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;
use Drupal\dcb\Service\DCBCore;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @File: Controller for necessary custom ajax callback routes. Used for updating
 * weights of components, deleting components and clearing entity caches.
 */

/**
 * Class DCBController.
 *
 * @package Drupal\dcb\Controller
 */
class DCBController extends ControllerBase {

  /**
   * DCB core service.
   *
   * @var \Drupal\dcb\Service\DCBCore
   */
  public $DCBCore;

  /**
   * {@inheritdoc}
   */
  public function __construct(DCBCore $DCBCore) {
    $this->DCBCore = $DCBCore;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dcb.core')
    );
  }

  /**
   * Callback to delete a DCB component. Requires Region ID and Block ID.
   *
   * @param $rid
   * @param $bid
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function remove($rid, $bid) {
    $result = $this->DCBCore->removeBlock($rid, $bid);
    return new JsonResponse(Json::encode($result));
  }

  /**
   * Callback to update weights of DCB components.
   *
   * @param $rid
   * @param $bid
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function update($rid, $bid, $revision, $weight) {
    $result = $this->DCBCore->updateWeight($rid, $bid, $revision, $weight);
    return new JsonResponse(Json::encode($result));
  }

  /**
   * Clear entity cache tag callback.
   *
   * Callback to clear entity cache tag of a specific entity when something
   * has been updated.
   *
   * @param $etype
   * @param $eid
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function invalidateEntityCache($etype, $eid) {
    $result = $this->DCBCore->invalidateCache($etype, $eid);
    return new JsonResponse(Json::encode($result));
  }

}
