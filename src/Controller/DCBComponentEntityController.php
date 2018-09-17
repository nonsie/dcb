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

  /**
   * Displays add links for the available DCB component types.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @param string $parent_id
   *   The parent ID.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
   *   A render array with the add links for each bundle.
   */
  public function modalAddPage($entity_type_id, $parent_id) {
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
    $bundle_key = $entity_type->getKey('bundle');
    $bundle_entity_type_id = $entity_type->getBundleEntityType();
    $build = [
      '#theme' => 'entity_add_list',
      '#bundles' => [],
    ];
    if ($bundle_entity_type_id) {
      $bundle_argument = $bundle_entity_type_id;
      $bundle_entity_type = $this->entityTypeManager->getDefinition($bundle_entity_type_id);
      $bundle_entity_type_label = $bundle_entity_type->getLowercaseLabel();
      $build['#cache']['tags'] = $bundle_entity_type->getListCacheTags();

      // Build the message shown when there are no bundles.
      $link_text = $this->t('Add a new @entity_type.', ['@entity_type' => $bundle_entity_type_label]);
      $link_route_name = 'entity.' . $bundle_entity_type->id() . '.add_form';
      $build['#add_bundle_message'] = $this->t('There is no @entity_type yet. @add_link', [
        '@entity_type' => $bundle_entity_type_label,
        '@add_link' => Link::createFromRoute($link_text, $link_route_name)->toString(),
      ]);
      // Filter out the bundles the user doesn't have access to.
      $access_control_handler = $this->entityTypeManager->getAccessControlHandler($entity_type_id);
      foreach ($bundles as $bundle_name => $bundle_info) {
        $access = $access_control_handler->createAccess($bundle_name, NULL, [], TRUE);
        if (!$access->isAllowed()) {
          unset($bundles[$bundle_name]);
        }
        $this->renderer->addCacheableDependency($build, $access);
      }
      // Add descriptions from the bundle entities.
      $bundles = $this->loadBundleDescriptions($bundles, $bundle_entity_type);
    }
    else {
      $bundle_argument = $bundle_key;
    }

    $form_route_name = 'entity.' . $entity_type_id . '.add_form';
    $destination = $this->redirectDestination->getAsArray();

    // Prepare the #bundles array for the template.
    foreach ($bundles as $bundle_name => $bundle_info) {
      $build['#bundles'][$bundle_name] = [
        'label' => $bundle_info['label'],
        'description' => isset($bundle_info['description']) ? $bundle_info['description'] : '',
        'add_link' => Link::createFromRoute(
          $bundle_info['label'],
          $form_route_name,
          [$bundle_argument => $bundle_name, 'parent_id' => $parent_id, 'dcb_component_type' => $bundle_name, 'destination' => $destination['destination']],
          ['attributes' => ['data-dialog-type' => 'modal', 'class' => ['use-ajax']]]
        ),
      ];
    }

    return $build;
  }

}
