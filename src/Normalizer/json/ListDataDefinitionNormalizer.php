<?php

namespace Drupal\dcb\Normalizer\json;

use Drupal\schemata_json_schema\Normalizer\json\ListDataDefinitionNormalizer as JsonApiListDataDefinitionNormalizer;

/**
 * Normalizer for ListDataDefinitionInterface objects.
 *
 * Almost all entity properties in the system are a list of values, each value
 * in the "List" might be a ComplexDataDefinitionInterface (an object) or it
 * might be more of a scalar.
 */
class ListDataDefinitionNormalizer extends JsonApiListDataDefinitionNormalizer {


  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $normalized = parent::normalize($entity, $format, $context);
    // FieldDefinitionInterface::isRequired() explicitly indicates there must be
    // at least one item in the list. Extending this reasoning, the same must be
    // true of all ListDataDefinitions.
//    if ($this->requiredProperty($entity)) {
//      $property['minItems'] = 1;
//    }
    return $normalized;
  }

}
