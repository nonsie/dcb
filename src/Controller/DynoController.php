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
   * @param $type
   * @param $rid
   * @param $nid
   * @return JsonResponse
   */
  function generate($type, $rid, $nid) {
    $form = DynoBlockForms::generateForm($type, $rid, $nid);
    $form['html'] = render($form['form']);
    return new JsonResponse(Json::encode($form));
  }

  /**
   * @return JsonResponse
   */
  function selectorModal() {
    $modal = new DynoblockWidgetModal();
    $modal->init();
    $response = array(Json::encode(array(
      'html' => render($modal->modal),
      'sections' => $modal->build(),
      'widgets' => $modal->widgets,
      'themes' => $modal->themes,
      'default_active' => $modal->default_active,
    )));
    return new JsonResponse($response);
  }

  /**
   * @param $method
   * @return JsonResponse
   */
  function save($method) {
    $result = $this->dynoblockCore->saveBlock($method);
    return new JsonResponse(Json::encode($result));
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
   * @param $nid
   *
   * @return JsonResponse
   */
  function edit($rid, $bid, $nid) {
    $form = $this->dynoblockCore->editBlock($rid, $bid, $nid);
    $form['html'] = render($form['form']);
    return new JsonResponse(Json::encode($form));
  }

  /**
   * @param string $type
   * @param $id
   */
  function ajaxLoad($type = 'blocks', $id) {

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

  /**
   * @return array
   */
  function testpage() {

    $build = [
      '#type' => 'link',
      '#title' => $this->t('Add Item'),
      '#url' => Url::fromRoute('dynoblock.admin.wizard.ajax.step', array('step' => 'selectgroup', 'rid' => 'testrid')),
      '#attributes' => [
        'class' => ['button', 'use-ajax'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode(['width' => 800, 'height' => 600]),
      ],
    ];

    return $build;
  }

}
