<?php

namespace Drupal\dcb\Renderer;

use Drupal\dcb\Manager\DCBComponentManager;

class DrupalThemeRenderer implements DCBRendererInterface {

  /**
   * @var \Drupal\dcb\Manager\DCBComponentManager
   */
  private $componentManager;

  public function __construct(DCBComponentManager $componentManager) {
    $this->componentManager = $componentManager;
  }

  public function renderRegion($id, $entity, $region_label, $prerenderdata){
    $renderarray = $this->renderRegionContainer($id, $entity, $region_label);
    foreach ($prerenderdata as $component_data) {
      $renderarray['components'][] = $this->renderComponent($component_data);
    }
    return $renderarray;
  }

  public function renderRegionContainer($rid, $eid = NULL, $label = NULL) {
    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['dcb-region'],
        'data-dcb-rid' => $rid,
        'data-dcb-label' => $label,
        'data-dcb-eid' => $eid,
      ],
    ];
  }

  public function renderComponent($component_data) {

    $render_data = [
      '#type' => 'container',
      '#weight' => 0,
      '#attributes' => [
        'class' => ['dcb-component'],
        'data-dcb-bid' => $component_data['meta']['bid'],
        'data-dcb-rid' => $component_data['meta']['rid'],
        'data-dcb-handler' => $component_data['meta']['component'],
        'data-dcb-weight' => 0,
        'data-dcb-label' => $component_data['meta']['component'],
      ],
    ];

    $render_data['inner_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['dcb-content'],
      ],
    ];

    $render_data['inner_container']['theme'] = [
      '#theme' => 'dcb_component',
      '#component_data' => $component_data,
    ];

    return $render_data;

  }

  public function onHookTheme($existing, $type, $theme, $path) {

  }

  public function onHookThemeSuggestionDcbComponent($variables) {

  }

}
