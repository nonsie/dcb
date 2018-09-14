<?php

namespace Drupal\dcb;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\dcb\Entity\DcbInterface;

/**
 * Defines the storage handler class for DCB entities.
 *
 * This extends the base storage class, adding required special handling for
 * DCB entities.
 *
 * @ingroup dcb
 */
interface DCBStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of DCB revision IDs for a specific DCB.
   *
   * @param \Drupal\dcb\Entity\DcbInterface $entity
   *   The DCB entity.
   *
   * @return int[]
   *   DCB revision IDs (in ascending order).
   */
  public function revisionIds(DcbInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as DCB author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   DCB revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\dcb\Entity\DcbInterface $entity
   *   The DCB entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(DcbInterface $entity);

  /**
   * Unsets the language for all DCB with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
