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

  private $bid;

  public function __construct(DCBFieldManager $DCBFieldManager) {
    $this->DCBFieldManager = $DCBFieldManager;
  }

  public function generate(FormStateInterface $formState, DCBComponentInterface $component) {
    $this->formState = $formState;
    $this->component = $component;
    $this->bid = $this->generateBid($this->formState->getValue(['meta','bid']));
    return [
      'bid' => $this->bid,
      'rid' => $this->formState->getValue(['meta','rid']),
      'weight' => $this->formState->getValue(['meta','weight']),
      'revision' => $this->formState->getValue(['meta','revision']),
      'status' => $this->formState->getValue(['meta','status']),
      'data' => [
        'meta' => $this->buildMeta(),
        'fieldSets' => [
          'outer' => [
            'fields' => [
              'visible' => $this->buildOuterFieldData(),
            ],
          ],
        ],
      ],
    ];
  }

  protected function generateBid($bidvalue) {
    if ($this->formState->getValue(['meta','bid']) === 'new') {
      return time();
    }
    else {
      return $this->formState->getValue(['meta','bid']);
    }
  }

  public function buildMeta() {
    $form_state_meta = $this->formState->getValue('meta');
    foreach($form_state_meta as $key => $value) {
      $meta[$key] = $value;
    }
    $meta['bid'] = $this->bid;
    $meta['last_update'] = time();
    return $meta;
  }

  public function buildOuterFieldData() {
    $outerfields = $this->component->getOuterFieldsDefinition();
    $storage = [];
    foreach ($outerfields as $key => $type) {
      /** @var \Drupal\dcb\Base\Field\DCBFieldBase $field */
      $field = $this->DCBFieldManager->createInstance($type);
      $storage[$key]['field_data'] = $field->prepareStorage($this->formState->getValue($key));
    }
    return $storage;
  }

}
