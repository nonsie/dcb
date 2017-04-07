<?php

# @File: not much in here right now, but may be helpful in the future.

namespace Drupal\dynoblock\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dynoblock\Service\DynoblockCore;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ComponentWizardBaseForm extends FormBase {

  /**
   * @var \Drupal\dynoblock\Service\DynoblockCore
   */
  public $core;


  /**
   * SelectGroup constructor.
   * @param \Drupal\dynoblock\Service\DynoblockCore $core
   */
  public function __construct(DynoblockCore $core) {
    $this->core = $core;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dynoblock.core')
    );
  }

}
