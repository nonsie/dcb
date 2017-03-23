<?php
namespace Drupal\dynoblock\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'dynoblock' field type.
 *
 * @FieldType(
 *   id = "dynoblock",
 *   label = @Translation("Dynoblock"),
 *   description = @Translation("This field stores a dynoblock instance in the
 * database."),
 *   category = @Translation("Text"),
 *   default_widget = "dynoblock_default",
 *   default_formatter = "basic_string"
 * )
 */
class DynoblockItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'id' => array(
          'type'   => 'varchar',
          'length' => 256,
        ),
      ),
      'indexes' => array(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['id'] = DataDefinition::create('string')
      ->setLabel(t('Dynoblock'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }
}