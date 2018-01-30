<?php

namespace Drupal\dcb\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dcb\Controller\DCBRegionController;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'dcb_default' formatter.
 *
 * @FieldFormatter(
 *   id = "dcb_default",
 *   label = @Translation("DCB default formatter"),
 *   field_types = {
 *     "dcb"
 *   }
 * )
 */
class DCBDefaultFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\dcb\Controller\DCBRegionController
   *   Injected Region controller.
   */
  protected $DCBRegionController;

  /**
   * DCBDefaultFormatter constructor.
   *
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   * @param array $settings
   * @param string $label
   * @param string $view_mode
   * @param array $third_party_settings
   * @param \Drupal\dcb\Controller\DCBRegionController $DCBRegionController
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, DCBRegionController $DCBRegionController) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->DCBRegionController = $DCBRegionController;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition){
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('dcb.region.controller')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = t('Displays DCB region ID.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    /**
     * @var mixed $delta
     * @var FieldItemListInterface $item
     */
    foreach ($items as $delta => $item) {
      $element[$delta] = $this->DCBRegionController->renderRegion($item->id, $item->getEntity()->id(), $item->getFieldDefinition()->getLabel());
    }
    return $element;
  }

}
