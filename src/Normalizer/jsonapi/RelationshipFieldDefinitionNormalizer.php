<?php

namespace Drupal\dcb\Normalizer\jsonapi;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\schemata_json_schema\Normalizer\jsonapi\RelationshipFieldDefinitionNormalizer as JsonApiRelationshipFieldDefinitionNormalizer;

/**
 * Normalizer for RelationshipFieldDefinitionNormalizer objects.
 *
 * This normalizes the JSON API relationships. This normalizer shortcuts the
 * recursion for the entity reference field. A JSON API relationship is what it
 * is, regardless of how Drupal stores the relationship.
 */
class RelationshipFieldDefinitionNormalizer extends JsonApiRelationshipFieldDefinitionNormalizer {

  /**
   * Normalizes the relationship.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   *
   * @return array
   *   The normalized relationship.
   */
  protected function normalizeRelationship(FieldDefinitionInterface $field_definition) {
    $normalized = parent::normalizeRelationship($field_definition);
    $normalized['settings'] = $field_definition->getSettings();
    return $normalized;
  }

}
