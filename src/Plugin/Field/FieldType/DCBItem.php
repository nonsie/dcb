<?php

namespace Drupal\dcb\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'dcb' field type.
 *
 * @File: Plugin implementation of the 'dcb' field type.
 *
 * @FieldType(
 *   id = "dcb",
 *   label = @Translation("DCB Field"),
 *   description = @Translation("This field stores a dcb instance in the
 * database."),
 *   module = "dcb",
 *   category = @Translation("General"),
 *   default_widget = "dcb_default",
 *   default_formatter = "dcb_default"
 * )
 */
class DCBItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'id' => [
          'type' => 'varchar',
          'length' => 256,
          'not null' => FALSE,
        ],
      ],
      'indexes' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('id')->getValue();
    return empty($value) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['id'] = DataDefinition::create('string')
      ->setLabel(t('DCB'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    $values['id'] = md5($random->string() . time());
    return $values;
  }

}
