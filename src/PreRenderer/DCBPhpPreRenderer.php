<?php

namespace Drupal\dcb\PreRenderer;

use Drupal\dcb\Base\Component\DCBComponentInterface;
use Drupal\dcb\Manager\DCBFieldManager;

class DCBPhpPreRenderer {

  /**
   * @var \Drupal\dcb\Base\Component\DCBComponentInterface
   */
  private $component;

  /**
   * @var \Drupal\dcb\Manager\DCBFieldManager
   */
  private $DCBFieldManager;

  public function __construct(DCBFieldManager $DCBFieldManager) {
    $this->DCBFieldManager = $DCBFieldManager;
  }

  public function preRender(DCBComponentInterface $component){
    // for each of the fields in this component, run the preRender method.
    // then pass that data to preRender on the component itself for any final modification before returning.
    $this->component = $component;

    $assembled = [
      'meta' => $this->component->getInstanceData()['meta'],
      'display' => '',
      'outer' => $this->outerPreRender(),
    ];

    return $this->component->preRender($assembled);
  }

  public function outerPreRender() {
    $outerfields = $this->component->getOuterFieldsDefinition();
    foreach ($outerfields as $key => $type) {
      /** @var \Drupal\dcb\Base\Field\DCBFieldBase $field */
      $field = $this->DCBFieldManager->createInstance($type);
      $data[$key] = $field->preRender($this->component->getFieldProperties($key), $this->component->getOuterFieldsInstanceData($key));
    }
    return !empty($data) ? $data : [];
  }

}
