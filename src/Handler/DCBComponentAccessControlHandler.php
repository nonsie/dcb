<?php

namespace Drupal\dcb\Handler;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the DCB Component entity.
 *
 * @see \Drupal\dcb\Entity\DCBComponent.
 */
class DCBComponentAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\dcb\Entity\DCBComponentInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished dcb component entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published dcb component entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit dcb component entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete dcb component entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add dcb component entities');
  }

}
