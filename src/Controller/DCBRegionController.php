<?php
/**
 * Created by PhpStorm.
 * User: garymorse
 * Date: 10/2/17
 * Time: 7:46 PM
 */

namespace Drupal\dcb\Controller;

use Drupal\Core\Cache\CacheTagsInvalidator;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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

    $query = \Drupal::entityQuery('dcb_component');
    $query->condition('status', 1);
    $query->condition('region_id', $rid);
    $ids = $query->execute();

    $view_builder = $this->entityTypeManager->getViewBuilder('dcb_component');
    $entity = $this->entityTypeManager->getStorage('dcb_component')->loadMultiple(array_values($ids));
    $pre_render = $view_builder->viewMultiple($entity, 'view_mode_selector');

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
        'max-age' => \Drupal\Core\Cache\Cache::PERMANENT,
        'tags' => ['dcbregion:' . $rid],
      ],
    ];

    $region['content'] = $pre_render;

    return $region;

  }

  public function deleteComponentFromRegion($regionId, $componentId) {
    $this->entityTypeManager->getStorage('dcb_component')->delete([$componentId]);
    $this->cacheTagsInvalidator->invalidateTags(['dcbregion:' . $regionId]);
    return new JsonResponse(TRUE);
  }

}
