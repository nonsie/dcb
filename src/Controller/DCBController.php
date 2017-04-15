<?php

namespace Drupal\dcb\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;
use Drupal\dcb\Service\DCBCore;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;


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
   * @param $rid
   * @param $bid
   *
   * @return JsonResponse
   */
  function remove($rid, $bid) {
    $result = $this->DCBCore->removeBlock($rid, $bid);
    return new JsonResponse(Json::encode($result));
  }

  /**
   * @param $rid
   * @param $bid
   *
   * @return JsonResponse
   */
  function update($rid, $bid) {
    $result = $this->DCBCore->updateBlock($rid, $bid);
    return new JsonResponse(Json::encode($result));
  }

  /**
   * @param $etype
   * @param $eid
   * @return JsonResponse
   */
  function invalidateEntityCache($etype, $eid) {
    $result = $this->DCBCore->invalidateCache($etype, $eid);
    return new JsonResponse(Json::encode($result));
  }

}
