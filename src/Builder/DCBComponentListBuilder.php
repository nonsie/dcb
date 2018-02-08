<?php

namespace Drupal\dcb\Builder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of DCB Component entities.
 *
 * @ingroup dcb
 */
class DCBComponentListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('DCB Component ID');
    $header['administrative_label'] = $this->t('Administrative Label');
    $header['name'] = $this->t('DCB Component Type');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\dcb\Entity\DCBComponent */
    $row['id'] = $entity->id();
    $row['administrative_label'] = $entity->getAdministrativeLabel()->getString();
    $row['name'] = $entity->label();
    return $row + parent::buildRow($entity);
  }

}
