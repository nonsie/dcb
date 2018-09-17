<?php

namespace Drupal\dcb\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheTagsInvalidator;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dcb\Controller\DCBController;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'dcbfield_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "dcbfield_formatter",
 *   label = @Translation("DCB UI formatter"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class DCBFieldFormatter extends EntityReferenceFormatterBase implements ContainerFactoryPluginInterface {


  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a EntityReferenceEntityFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // Implement default settings.
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      // Implement settings form.
    ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    // Implement settings summary.

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      if ($entity->id()) {
        // Entity this DCB is part of.
        $parent_entity = $items->getEntity()->id();
        // Region name.
        $label = $entity->label();
        // Region ID.
        $id = $entity->id();
        $region = [
          '#type' => 'container',
            '#attributes' => [
            'class' => ['dcb-region'],
            'data-dcb-rid' => $id,
            'data-dcb-label' => $label,
            'data-dcb-eid' => $parent_entity,
          ],
          '#cache' => [
            'keys' => ['dcbregion', $id],
            'max-age' => Cache::PERMANENT,
            'tags' => ['dcbregion:' . $id],
          ],
          /**
           * '#cache' => [
            'tags' => $entity->getCacheTags(),
          ],
           */
        ];

        // Get view builder for dcb components.
        
        $view_builder = $this->entityTypeManager->getViewBuilder('dcb_component');
        // Get list of components in this region (parent > children).
        // @todo Find a better way with entityQuery. ATM it seems unable to query by custom field.
        /**$query = \Drupal::entityQuery('dcb_component')
        ->condition('status', 1)
        ->condition('parent_id', $id)
        ->sort('weight','ASC');
        $ids = $query->execute();
        */
        $query = \Drupal::database()->select('dcb_component', 'dc');
        $query->leftjoin('dcb_component_field_data', 'dcfd', 'dcfd.id = dc.id');
        $query->fields('dc', ['id']);
        $query->condition('dcfd.status', 1);
        $query->condition('dcfd.parent_id__target_id', $id);
        $query->orderBy('dcfd.weight', 'ASC');
        $ids = $query->execute()->fetchAllKeyed(0, 0);

        $components = $this->entityTypeManager->getStorage('dcb_component')->loadMultiple(array_values($ids));

        $pre_render = $view_builder->viewMultiple($components, 'dcb_inline_viewmode');
        $region['content'] = $pre_render;
        $elements[$delta] = $region;
      }
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    //
  }

  /**
   * @todo This needs to run a proper revision query
   * 
   * @param $id
   *
   * @return array
   */
  protected function getComponentsByWeight($id) {
    /**$entities = \Drupal::entityTypeManager()->getStorage('dcb_component')
    ->loadByProperties(['status' => 1]);

    return ($entities) ? $entities : NULL;*/

    $query = \Drupal::entityQuery('dcb_component');
    $query->condition('status', 1);
    //$query->condition('parent_id', $id);
    //$query->sort('weight','ASC');
    $ids = $query->execute();
    return $ids;
  }

}
