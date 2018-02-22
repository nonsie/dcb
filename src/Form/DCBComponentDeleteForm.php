<?php

namespace Drupal\dcb\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Cache\CacheTagsInvalidator;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting DCB Component entities.
 *
 * @ingroup dcb
 */
class DCBComponentDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * @var \Drupal\Core\Cache\CacheTagsInvalidator
   */
  protected $cacheTagsInvalidator;

  public function __construct(EntityManagerInterface $entity_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, CacheTagsInvalidator $cacheTagsInvalidator) {
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('cache_tags.invalidator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this
      ->t('Are you sure you want to delete DCB component %name?', [
        '%name' => $this->entity->label(),
      ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.dcb_component.collection');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRedirectUrl() {
    return $this->getCancelUrl();
  }

  /**
   * {@inheritdoc}
   */
  protected function getDeletionMessage() {
    return $this
      ->t('DCB component %label has been deleted.', [
        '%label' => $this->entity->label(),
      ]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getEntity();

    // Make sure that deleting a translation does not delete the whole entity.
    if (!$entity->isDefaultTranslation()) {
      $untranslated_entity = $entity->getUntranslated();
      $untranslated_entity->removeTranslation($entity->language()->getId());
      $untranslated_entity->save();
    }
    else {
      $entity->delete();
    }
    $form_state->setRedirectUrl($this->getRedirectUrl());

    // Bust cache for the region.
    $this->cacheTagsInvalidator->invalidateTags(['dcbregion:' . $entity->get('region_id')->getString()]);
  }

}
