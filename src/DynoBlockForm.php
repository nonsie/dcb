<?php

namespace Drupal\dynoblock;

interface DynoBlockForm {
  public function init();
  public function build($form_state = array());
  public function widgetForm(&$form_state = array(), $items, $delta);
  public function formSubmit(&$form_state);
  public function render();
}