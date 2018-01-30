<?php

namespace Drupal\dcb\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining DCB Component entities.
 *
 * @ingroup dcb
 */
interface DCBComponentInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the DCB Component name.
   *
   * @return string
   *   Name of the DCB Component.
   */
  public function getName();

  /**
   * Sets the DCB Component name.
   *
   * @param string $name
   *   The DCB Component name.
   *
   * @return \Drupal\dcb\Entity\DCBComponentInterface
   *   The called DCB Component entity.
   */
  public function setName($name);

  /**
   * Gets the DCB Component creation timestamp.
   *
   * @return int
   *   Creation timestamp of the DCB Component.
   */
  public function getCreatedTime();

  /**
   * Sets the DCB Component creation timestamp.
   *
   * @param int $timestamp
   *   The DCB Component creation timestamp.
   *
   * @return \Drupal\dcb\Entity\DCBComponentInterface
   *   The called DCB Component entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the DCB Component published status indicator.
   *
   * Unpublished DCB Component are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the DCB Component is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a DCB Component.
   *
   * @param bool $published
   *   TRUE to set this DCB Component to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\dcb\Entity\DCBComponentInterface
   *   The called DCB Component entity.
   */
  public function setPublished($published);

  /**
   * Gets the DCB Component revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the DCB Component revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\dcb\Entity\DCBComponentInterface
   *   The called DCB Component entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the DCB Component revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the DCB Component revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\dcb\Entity\DCBComponentInterface
   *   The called DCB Component entity.
   */
  public function setRevisionUserId($uid);

}
