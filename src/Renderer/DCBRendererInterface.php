<?php

namespace Drupal\dcb\Renderer;


interface DCBRendererInterface {

  function renderRegion($id, $entity, $region_label, $prerenderdata);

  function renderComponent($component_data);

}
