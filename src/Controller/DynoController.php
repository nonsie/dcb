<?php

namespace Drupal\dynoblock\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\dynoblock\DynoBlockForms;
use Drupal\dynoblock\DynoblockWidgetModal;
use Drupal\Component\Serialization\Json;
use Drupal\dynoblock\Service\DynoblockCore;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;


class DynoController extends ControllerBase {

  /**
   * Dynoblock core.
   *
   * @var \Drupal\dynoblock\Service\DynoblockCore
   */
  public $dynoblockCore;


  /**
   * {@inheritdoc}
   */
  public function __construct(DynoblockCore $dynoblockCore) {
    $this->dynoblockCore = $dynoblockCore;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dynoblock.core')
    );
  }

  /**
   * @param $rid
   * @param $bid
   *
   * @return JsonResponse
   */
  function remove($rid, $bid) {
    $result = $this->dynoblockCore->removeBlock($rid, $bid);
    return new JsonResponse(Json::encode($result));
  }

  /**
   * @param $rid
   * @param $bid
   *
   * @return JsonResponse
   */
  function update($rid, $bid) {
    $result = $this->dynoblockCore->updateBlock($rid, $bid);
    return new JsonResponse(Json::encode($result));
  }

  /**
   * @param $etype
   * @param $eid
   * @return JsonResponse
   */
  function invalidateEntityCache($etype, $eid) {
    $result = $this->dynoblockCore->invalidateCache($etype, $eid);
    return new JsonResponse(Json::encode($result));
  }

}
