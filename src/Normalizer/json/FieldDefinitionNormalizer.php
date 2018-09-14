<?php

namespace Drupal\dcb\Normalizer\json;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\schemata_json_schema\Normalizer\json\FieldDefinitionNormalizer as JsonApiFieldDefinitionNormalizer;

/**
 * Normalizer for FieldDefinitionInterface objects.
 *
 * This normalizes the variant of data fields particular to the Field system.
 * By accessing this via the FieldDefinitionInterface, there is greater access
 * to some of the methods providing deeper schema properties.
 */
class FieldDefinitionNormalizer extends JsonApiFieldDefinitionNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = '\Drupal\Core\Field\FieldDefinitionInterface';

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    /* @var $entity \Drupal\Core\Field\FieldDefinitionInterface */
    $normalized = parent::normalize($entity, $format, $context);
    $normalized['properties'][$context['name']]['settings'] = $entity->getSettings();
    return $normalized;
  }

}
