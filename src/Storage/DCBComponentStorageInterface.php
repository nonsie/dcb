<?php

namespace Drupal\dcb\Storage;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface DCBComponentStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of DCB Component revision IDs for a specific DCB Component.
   *
   * @param \Drupal\dcb\Entity\DCBComponentInterface $entity
   *   The DCB Component entity.
   *
   * @return int[]
   *   DCB Component revision IDs (in ascending order).
   */
  public function revisionIds(DCBComponentInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as DCB Component author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   DCB Component revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\dcb\Entity\DCBComponentInterface $entity
   *   The DCB Component entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(DCBComponentInterface $entity);

  /**
   * Unsets the language for all DCB Component with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
