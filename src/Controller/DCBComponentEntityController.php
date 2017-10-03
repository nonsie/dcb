<?php

namespace Drupal\dcb\Controller;

use Drupal\Core\Entity\Controller\EntityController;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RedirectDestination;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DCBComponentEntityController extends EntityController {

  /**
   * @var \Drupal\Core\Routing\RedirectDestination
   */
  private $redirectDestination;

  public function __construct(
      EntityTypeManagerInterface $entity_type_manager,
      EntityTypeBundleInfoInterface $entity_type_bundle_info,
      EntityRepositoryInterface $entity_repository,
      RendererInterface $renderer,
      TranslationInterface $string_translation,
      UrlGeneratorInterface $url_generator,
      RedirectDestination $redirectDestination
  ) {
    parent::__construct($entity_type_manager, $entity_type_bundle_info, $entity_repository, $renderer, $string_translation, $url_generator);
    $this->redirectDestination = $redirectDestination;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity.repository'),
      $container->get('renderer'),
      $container->get('string_translation'),
      $container->get('url_generator'),
      $container->get('redirect.destination')
    );
  }

  public function modalAddPage($entity_type_id, $region_id) {
    $build = parent::addPage($entity_type_id);
    $form_route_name = 'entity.' . $entity_type_id . '.add_form';
    $destination = $this->redirectDestination->getAsArray();
    if (isset($build['#bundles'])) {
      foreach ($build['#bundles'] as $bundle_name => $bundle_render){
        $build['#bundles'][$bundle_name]['add_link'] = Link::createFromRoute(
          $build['#bundles'][$bundle_name]['label'],
          $form_route_name,
          ['dcb_component_type' => $bundle_name, 'region_id' => $region_id, 'destination' => $destination['destination']],
          ['attributes' => ['data-dialog-type' => 'modal', 'class' => ['use-ajax']]]
        );
      }
    }
    else {
      $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
      $bundle_names = array_keys($bundles);
      $bundle_name = reset($bundle_names);
      return $this->redirect($form_route_name, ['dcb_component_type' => $bundle_name, 'region_id' => $region_id]);
    }

    return $build;

  }

}
