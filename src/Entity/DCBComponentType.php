<?php

namespace Drupal\dcb\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the DCB Component type entity.
 *
 * @ConfigEntityType(
 *   id = "dcb_component_type",
 *   label = @Translation("DCB Component type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\dcb\Builder\DCBComponentTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\dcb\Form\DCBComponentTypeForm",
 *       "edit" = "Drupal\dcb\Form\DCBComponentTypeForm",
 *       "delete" = "Drupal\dcb\Form\DCBComponentTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\dcb\Provider\DCBComponentTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "dcb_component_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "dcb_component",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/dcb_component_type/{dcb_component_type}",
 *     "add-form" = "/admin/structure/dcb_component_type/add",
 *     "edit-form" = "/admin/structure/dcb_component_type/{dcb_component_type}/edit",
 *     "delete-form" = "/admin/structure/dcb_component_type/{dcb_component_type}/delete",
 *     "collection" = "/admin/structure/dcb_component_type"
 *   }
 * )
 */
class DCBComponentType extends ConfigEntityBundleBase implements DCBComponentTypeInterface {

  /**
   * The DCB Component type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The DCB Component type label.
   *
   * @var string
   */
  protected $label;

  /**
   * @var array
   */
  protected $selectedViewModes;

  /**
   * @param array $viewModes
   */
  public function setSelectedViewModes(array $viewModes) {
    $this->selectedViewModes = $viewModes;
  }

  /**
   * @return array
   */
  public function getSelectedViewModes(): array {
    return $this->selectedViewModes;
  }

}
