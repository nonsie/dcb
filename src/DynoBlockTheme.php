<?php

namespace Drupal\dynoblock;

interface DynoBlockTheme {
  public function globalSettings(&$widget_form, &$form_state);
  public function preRender($widget, $values, &$output, $theme_settings = array());
}