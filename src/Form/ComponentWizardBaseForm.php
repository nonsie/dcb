<?php

namespace Drupal\dcb\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Render\Renderer;
use Drupal\dcb\Service\DCBCore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @File: Base form that all steps of the wizard extend. Contains functions
 * necessary for creating the DCB admin forms.
 */

/**
 * Class ComponentWizardBaseForm.
 *
 * @package Drupal\dcb\Form
 */
abstract class ComponentWizardBaseForm extends FormBase {

  /**
   * @var \Drupal\dcb\Service\DCBCore
   */
  protected $core;

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;


  /**
   * SelectGroup constructor.
   *
   * @param \Drupal\dcb\Service\DCBCore $core
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   */
  public function __construct(DCBCore $core, RequestStack $request, Renderer $renderer) {
    $this->core = $core;
    $this->request = $request;
    $this->renderer = $renderer;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dcb.core'),
      $container->get('request_stack'),
      $container->get('renderer')
    );
  }

}
