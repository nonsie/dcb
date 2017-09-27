<?php

namespace Drupal\dcb\Renderer;

use Drupal\dcb\Manager\DCBComponentManager;

class DCBRendererFactory {

  /**
   * @var \Drupal\dcb\Manager\DCBComponentManager
   */
  private $componentManager;

  public function __construct(DCBComponentManager $componentManager) {
    $this->componentManager = $componentManager;
  }

  /**
   * @param string $renderer
   *
   * @return \Drupal\dcb\Renderer\DCBRendererInterface
   */
  public function getRenderer($renderer) {
    switch ($renderer) {
      default:
        return new DrupalThemeRenderer($this->componentManager);
    }
  }
}
