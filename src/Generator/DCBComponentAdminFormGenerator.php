<?php

namespace Drupal\dcb\Generator;

use Drupal\dcb\Base\Component\DCBComponentInterface;
use Drupal\dcb\Manager\DCBFieldManager;

class DCBComponentAdminFormGenerator {

  /**
   * @var array $adminForm
   *   The contents of this portion of the admin form.
   */
  private $adminForm = [];

  /**
   * @var \Drupal\dcb\Base\Component\DCBComponentBase $component
   *   Component plugin instance.
   */
  private $component;

  /**
   * @var \Drupal\dcb\Manager\DCBFieldManager
   */
  private $DCBFieldManager;

  /**
   * DCBComponentAdminFormGenerator constructor.
   *
   * @param \Drupal\dcb\Manager\DCBFieldManager $DCBFieldManager
   */
  public function __construct(DCBFieldManager $DCBFieldManager) {
    $this->DCBFieldManager = $DCBFieldManager;
  }

  /**
   * @param $regionId
   * @param $componentId
   * @param $entityId
   * @param \Drupal\dcb\Base\Component\DCBComponentInterface $component
   *
   * @return array
   */
  public function generate($regionId, $componentId, $entityId, DCBComponentInterface $component) {
    $this->component = $component;
    $this->adminForm = [
      'meta' => $this->generateMeta($regionId, $componentId, $entityId),
      'componentform' => [
        '#type' => 'container',
        'attributes' => [
          'id' => 'componentadminform',
        ],
        'outerfields' => $this->generateOuterFields(),
        'outerOptionalFields' => $this->generateOuterOptionalFields(),
        'items' => $this->generateRepeatingFields(),
      ],
    ];
    return $this->adminForm;
  }

  protected function generateMeta($regionId, $componentId, $entityId) {
    $component_data = $this->component->getInstanceData();
    return [
      '#tree' => TRUE,
      'eid' => [
        '#type' => 'hidden',
        '#value' => $entityId,
      ],
      'bid' => [
        '#type' => 'hidden',
        '#value' => $componentId,
      ],
      'rid' => [
        '#type' => 'hidden',
        '#value' => $regionId,
      ],
      'component' => [
        '#type' => 'hidden',
        '#value' => $this->component->getComponentTypeId(),
      ],
      'weight' => [
        '#type' => 'hidden',
        '#value' => isset($component_data['weight']) && !empty($component_data['weight']) ? $component_data['weight'] : '0',
      ],
      'revision' => [
        '#type' => 'hidden',
        '#value' => isset($component_data['revision']) && !empty($component_data['revision']) ? $component_data['revision'] : '1',
      ],
      'status' => [
        '#type' => 'hidden',
        '#value' => isset($component_data['status']) && !empty($component_data['status']) ? $component_data['status'] : 'live',
      ],
    ];
  }

  /**
   * @return array
   */
  protected function generateOuterFields() {
    $outerfields = $this->component->getOuterFieldsDefinition();
    foreach ($outerfields as $key => $type) {
      /** @var \Drupal\dcb\Base\Field\DCBFieldBase $field */
      $field = $this->DCBFieldManager->createInstance($type);
      $formdata[$key] = $field->form($this->component->getFieldProperties($key), $this->component->getOuterFieldsInstanceData($key));
    }
    return !empty($formdata) ? $formdata : [];
  }

  protected function generateOuterOptionalFields() {
    return [];
  }

  protected function generateRepeatingFields() {
    return [];
  }

}
