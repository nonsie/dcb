<?php

namespace Drupal\dcb\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheTagsInvalidator;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Url;
use Drupal\dcb\Entity\DcbInterface;

/**
 * Class DCBController.
 * 
 * Contains callback methods for dynamic DCB routes.
 */
class DCBController extends ControllerBase {
   /**
   * @var \Drupal\Core\Cache\CacheTagsInvalidator
   */
  private $cacheTagsInvalidator;
   /**
   * DCBRegionController constructor.
   *
   * @param Drupal\Core\Cache\CacheTagsInvalidator $cacheTagsInvalidator
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CacheTagsInvalidator $cacheTagsInvalidator) {
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('cache_tags.invalidator')
    );
  }

  /**
   * Displays a DCB  revision.
   *
   * @param int $dcb_revision
   *   The DCB  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($dcb_revision) {
    $dcb = $this->entityManager()->getStorage('dcb')->loadRevision($dcb_revision);
    $view_builder = $this->entityManager()->getViewBuilder('dcb');

    return $view_builder->view($dcb);
  }

  /**
   * Page title callback for a DCB  revision.
   *
   * @param int $dcb_revision
   *   The DCB  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($dcb_revision) {
    $dcb = $this->entityManager()->getStorage('dcb')->loadRevision($dcb_revision);
    return $this->t('Revision of %title from %date', ['%title' => $dcb->label(), '%date' => format_date($dcb->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a DCB .
   *
   * @param \Drupal\dcb\Entity\DcbInterface $dcb
   *   A DCB  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(DCBInterface $dcb) {
    $account = $this->currentUser();
    $langcode = $dcb->language()->getId();
    $langname = $dcb->language()->getName();
    $languages = $dcb->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $dcb_storage = $this->entityManager()->getStorage('dcb');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $dcb->label()]) : $this->t('Revisions for %title', ['%title' => $dcb->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all dcb revisions") || $account->hasPermission('administer dcb entities')));
    $delete_permission = (($account->hasPermission("delete all dcb revisions") || $account->hasPermission('administer dcb entities')));

    $rows = [];

    $vids = $dcb_storage->revisionIds($dcb);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\dcb\DcbInterface $revision */
      $revision = $dcb_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $dcb->getRevisionId()) {
          $link = $this->l($date, new Url('entity.dcb.revision', ['dcb' => $dcb->id(), 'dcb_revision' => $vid]));
        }
        else {
          $link = $dcb->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.dcb.translation_revert', ['dcb' => $dcb->id(), 'dcb_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.dcb.revision_revert', ['dcb' => $dcb->id(), 'dcb_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.dcb.revision_delete', ['dcb' => $dcb->id(), 'dcb_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['dcb_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }


  /**
   * @param $rid
   * @param $entity
   * @param $region_label
   *
   * @return mixed
   */
  public function renderRegion($rid, $entity_id, $region_label) {
    $ids = $this->getComponentsByWeight($rid);
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
   * @param $id
   * @param $componentId
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function deleteComponent(Request $request, $parentId, $componentId) {
    $entityStorage = $this->entityTypeManager->getStorage('dcb_component');
    $entity = $entityStorage->load($componentId);
    if ($entity) {
      $entityStorage->delete([$entity]);
      $this->cacheTagsInvalidator->invalidateTags(['dcbregion:' . $parentId]);
    }
    $data['removed'] = TRUE;
    return new JsonResponse($data);
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param $parentId
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function setWeights(Request $request, $parentId) {
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
    $this->cacheTagsInvalidator->invalidateTags(['dcbregion:' . $parentId]);
    return new JsonResponse($data);
  }

  /**
   * @param $parentId
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getWeights($parentId) {
    $components = $this->getComponentsByWeight($parentId);
    return new JsonResponse($components);
  }

  /**
   * @todo This needs to run a proper revision query
   * 
   * @param $parentId
   *
   * @return array
   */
  public function getComponentsByWeight($parentId) {
    /**$query = \Drupal::entityQuery('dcb_component')
    ->condition('parent_id', $parentId)
    ->condition('status', 1)
    ->sort('weight','ASC');
    $ids = $query->execute();*/
    /** 
     * @todo This needs to be entityQuery but it fails with revisions.
     */
    $query = \Drupal::database()->select('dcb_component', 'dc');
    $query->leftjoin('dcb_component_field_data', 'dcfd', 'dcfd.id = dc.id');
    $query->fields('dc', ['id']);
    $query->condition('dcfd.status', 1);
    $query->condition('dcfd.parent_id__target_id', $parentId);
    $query->orderBy('dcfd.weight', 'ASC');
    $ids = $query->execute()->fetchAllKeyed(0, 0);
  
    return $ids;
  }

}
