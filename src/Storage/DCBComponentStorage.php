<?php

namespace Drupal\dcb\Storage;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\dcb\Entity\DCBComponentInterface;

/**
 * Defines the storage handler class for DCB Component entities.
 *
 * This extends the base storage class, adding required special handling for
 * DCB Component entities.
 *
 * @ingroup dcb
 */
class DCBComponentStorage extends SqlContentEntityStorage implements DCBComponentStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(DCBComponentInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {dcb_component_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {dcb_component_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(DCBComponentInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {dcb_component_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('dcb_component_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
