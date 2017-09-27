<?php
/**
 * Created by PhpStorm.
 * User: garymorse
 * Date: 9/25/17
 * Time: 3:40 PM
 */

namespace Drupal\dcb\Generator;

use Drupal\Core\Form\FormStateInterface;
use Drupal\dcb\Base\Component\DCBComponentInterface;
use Drupal\dcb\Manager\DCBFieldManager;

class DCBComponentStorageArrayGenerator {
  /**
   * @var \Drupal\dcb\Manager\DCBFieldManager
   */
  private $DCBFieldManager;

  /**
   * @var \Drupal\Core\Form\FormStateInterface
   */
  private $formState;

  /**
   * @var \Drupal\dcb\Base\Component\DCBComponentInterface
   */
  private $component;

  public function __construct(DCBFieldManager $DCBFieldManager) {
    $this->DCBFieldManager = $DCBFieldManager;
  }

  public function generate(FormStateInterface $formState, DCBComponentInterface $component) {
    $this->formState = $formState;
    $this->component = $component;
    return [
      'meta' => $this->buildMeta(),
      'fieldSets' => [
        'outer' => [
          'fields' => [
            'visible' => $this->buildOuterFieldData(),
          ],
        ],
      ],
    ];
  }

  public function buildMeta() {
    if ($this->formState->getValue(['meta','bid']) === 'new') {
      $bid = time();
    }
    else {
      $bid = $this->formState->getValue(['meta','bid']);
    }
    $meta = [
      'eid' => $this->formState->getValue(['meta','eid']),
      'bid' => $bid,
      'rid' => $this->formState->getValue(['meta','rid']),
      'component' => $this->formState->getValue(['meta','component']),
      'last_update' => time(),
    ];
    return $meta;
  }

  public function buildOuterFieldData() {
    $outerfields = $this->component->getOuterFieldsDefinition();
    foreach ($outerfields as $key => $type) {
      /** @var \Drupal\dcb\Base\Field\DCBFieldBase $field */
      $field = $this->DCBFieldManager->createInstance($type);
      $storage[$key]['field_data'] = $field->prepareStorage($this->formState->getValue($key));
    }
    return $storage;
  }

}
