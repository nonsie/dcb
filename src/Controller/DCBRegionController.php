<?php

namespace Drupal\dcb\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheTagsInvalidator;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DCBRegionController extends ControllerBase {

  /**
   * @var \Drupal\Core\Config\Entity\Query\QueryFactory
   */
  private $queryFactory;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Cache\CacheTagsInvalidator
   */
  private $cacheTagsInvalidator;

  /**
   * DCBRegionController constructor.
   *
   * @param \Drupal\Core\Config\Entity\Query\QueryFactory $queryFactory
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   */
  public function __construct(QueryFactory $queryFactory, EntityTypeManager $entityTypeManager, CacheTagsInvalidator $cacheTagsInvalidator) {
    $this->queryFactory = $queryFactory;
    $this->entityTypeManager = $entityTypeManager;
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container){
    return new static(
      $container->get('entity.query'),
      $container->get('entity_type.manager'),
      $container->get('cache_tags.invalidator')
    );
  }

  /**
   * @param $rid
   * @param $entity
   * @param $region_label
   *
   * @return mixed
   */
  public function renderRegion($rid, $entity_id, $region_label) {
    $ids = $this->getRegionComponentsByWeight($rid);
    $view_builder = $this->entityTypeManager->getViewBuilder('dcb_component');
    $entity = $this->entityTypeManager->getStorage('dcb_component')->loadMultiple(array_values($ids));
    $pre_render = $view_builder->viewMultiple($entity, 'dcb_inline_viewmode');

    $region = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['dcb-region'],
        'data-dcb-rid' => $rid,
        'data-dcb-label' => $region_label,
        'data-dcb-eid' => $entity_id,
      ],
      '#cache' => [
        'keys' => ['dcbregion', $rid],
        'max-age' => Cache::PERMANENT,
        'tags' => ['dcbregion:' . $rid],
      ],
    ];

    $region['content'] = $pre_render;

    return $region;

  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param $regionId
   * @param $componentId
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function deleteComponentFromRegion(Request $request, $regionId, $componentId) {
    $entityStorage = $this->entityTypeManager->getStorage('dcb_component');
    $entity = $entityStorage->load($componentId);
    if ($entity) {
      $entityStorage->delete([$entity]);
      $this->cacheTagsInvalidator->invalidateTags(['dcbregion:' . $regionId]);
    }
    $data['removed'] = TRUE;
    return new JsonResponse($data);
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param $regionId
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function updateWeights(Request $request, $regionId) {
    $weightdata = $request->get('weights');

    if (!empty($weightdata)) {
      $entityStorage = $this->entityTypeManager->getStorage('dcb_component');
      foreach ($weightdata as $eid => $weight) {
        /** @var \Drupal\dcb\Entity\DCBComponent $entity */
        $entity = $entityStorage->load($eid);
        $entity->setWeight($weight);
        $entity->save();
      }
    }
    $data = TRUE;
    $this->cacheTagsInvalidator->invalidateTags(['dcbregion:' . $regionId]);
    return new JsonResponse($data);
  }

  /**
   * @param $regionId
   *
   * @return array
   */
  public function getRegionComponentsByWeight($regionId) {
    $query = \Drupal::entityQuery('dcb_component');
    $query->condition('status', 1);
    $query->condition('region_id', $regionId);
    $query->sort('weight','ASC');
    $ids = $query->execute();
    return $ids;
  }

}
