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
 *   label = @Translation("Dynoblock Field"),
 *   description = @Translation("This field stores a dynoblock instance in the
 * database."),
 *   module = "dynoblock",
 *   category = @Translation("Custom"),
 *   default_widget = "dynoblock_default",
 *   default_formatter = "dynoblock_default"
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
          'not null' => FALSE,
        ),
      ),
      'indexes' => array(),
    );
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
      ->setLabel(t('Dynoblock'));

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