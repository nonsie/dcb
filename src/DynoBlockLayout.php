<?php

namespace Drupal\dynoblock;

interface DynoBlockLayout {
  public function init($values);
  public function preRender($values);
  public function render();
}