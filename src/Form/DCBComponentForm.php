<?php

namespace Drupal\dcb\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheTagsInvalidator;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RedirectDestination;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for DCB Component edit forms.
 *
 * @ingroup dcb
 */
class DCBComponentForm extends ContentEntityForm {

  /**
   * @var \Drupal\Core\Cache\CacheTagsInvalidator
   */
  private $cacheTagsInvalidator;

  public function __construct(EntityManagerInterface $entity_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, CacheTagsInvalidator $cacheTagsInvalidator, RedirectDestination $redirectDestination) {
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
    $this->redirectDestination = $redirectDestination;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('cache_tags.invalidator'),
      $container->get('redirect.destination')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\dcb\Entity\DCBComponent */
    $form = parent::buildForm($form, $form_state);

    if (!$this->entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 10,
      ];
    }

    if (isset($form['revision_log_message']['widget'][0]['#weight'])) {
      $form['revision_log_message']['#weight'] = 200;
      $form['revision_log_message']['widget'][0]['#weight'] = 200;
    }

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime(REQUEST_TIME);
      $entity->setRevisionUserId(\Drupal::currentUser()->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    $status = parent::save($form, $form_state);

    $this->cacheTagsInvalidator->invalidateTags(['dcbregion:' . $form_state->getValue(['region_id', '0', 'value'])]);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label DCB Component.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label DCB Component.', [
          '%label' => $entity->label(),
        ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityFromRouteMatch(RouteMatchInterface $route_match, $entity_type_id) {
    if ($route_match->getRawParameter($entity_type_id) !== NULL) {
      $entity = $route_match->getParameter($entity_type_id);
    }
    else {
      $values = [];
      // If the entity has bundles, fetch it from the route match.
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      if ($bundle_key = $entity_type->getKey('bundle')) {
        if (($bundle_entity_type_id = $entity_type->getBundleEntityType()) && $route_match->getRawParameter($bundle_entity_type_id)) {
          $values[$bundle_key] = $route_match->getParameter($bundle_entity_type_id)->id();
        }
        elseif ($route_match->getRawParameter($bundle_key)) {
          $values[$bundle_key] = $route_match->getParameter($bundle_key);
        }
      }
      $values['region_id'] = $route_match->getParameter('region_id');
      $entity = $this->entityTypeManager->getStorage($entity_type_id)->create($values);
    }

    return $entity;
  }

}
