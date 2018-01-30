<?php

namespace Drupal\dcb\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for DCB Component entities.
 */
class DCBComponentViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
