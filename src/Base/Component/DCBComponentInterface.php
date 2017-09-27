<?php

namespace Drupal\dcb\Base\Component;


interface DCBComponentInterface {

  function getOuterFieldsDefinition();

  function getInstanceData();

  function getFieldProperties(string $key);

  function getOuterFieldsInstanceData(string $key);

  function register();

  function preRender(array $prerender_data);

  function setInstanceData(array $component_data);

}
