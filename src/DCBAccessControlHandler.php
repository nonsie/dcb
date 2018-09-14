<?php

namespace Drupal\dcb;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the DCB entity.
 *
 * @see \Drupal\dcb\Entity\DCB.
 */
class DCBAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\dcb\Entity\DcbInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished dcb entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published dcb entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit dcb entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete dcb entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add dcb entities');
  }

}
