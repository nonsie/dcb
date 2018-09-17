<?php

namespace Drupal\dcb\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining DCB entities.
 *
 * @ingroup dcb
 */
interface DCBInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the DCB name.
   *
   * @return string
   *   Name of the DCB.
   */
  public function getName();

  /**
   * Sets the DCB name.
   *
   * @param string $name
   *   The DCB name.
   *
   * @return \Drupal\dcb\Entity\DcbInterface
   *   The called DCB entity.
   */
  public function setName($name);

  /**
   * Gets the DCB creation timestamp.
   *
   * @return int
   *   Creation timestamp of the DCB.
   */
  public function getCreatedTime();

  /**
   * Sets the DCB creation timestamp.
   *
   * @param int $timestamp
   *   The DCB creation timestamp.
   *
   * @return \Drupal\dcb\Entity\DcbInterface
   *   The called DCB entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the DCB published status indicator.
   *
   * Unpublished DCB are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the DCB is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a DCB.
   *
   * @param bool $published
   *   TRUE to set this DCB to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\dcb\Entity\DcbInterface
   *   The called DCB entity.
   */
  public function setPublished($published);

  /**
   * Gets the DCB revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the DCB revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\dcb\Entity\DcbInterface
   *   The called DCB entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the DCB revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the DCB revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\dcb\Entity\DcbInterface
   *   The called DCB entity.
   */
  public function setRevisionUserId($uid);

}
