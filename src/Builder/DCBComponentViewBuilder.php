<?php
/**
 * Created by PhpStorm.
 * User: garymorse
 * Date: 9/29/17
 * Time: 12:25 AM
 */

namespace Drupal\dcb\Builder;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Class DcbComponentEntityViewBuilder
 *
 * @package Drupal\dcb
 */
class DCBComponentViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {

    $build['#attributes']['class'][] = 'dcb-component';
    $build['#attributes']['data-dcb-bid'] = $entity->id();
    /** @var  $entity \Drupal\dcb\Entity\DcbComponent  */
    $build['#attributes']['data-dcb-label'] = $entity->getAdministrativeLabel()->getString();

  }

}
