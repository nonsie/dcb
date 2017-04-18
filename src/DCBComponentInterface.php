<?php
/**
 * @File: DCB component interface. Defines the methods required for a DCB component
 * plugin instance.
 */

namespace Drupal\dcb;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\dcb\Form\ComponentWizardBaseForm;

/**
 * Interface DCBComponentInterface
 * @package Drupal\dcb
 */
interface DCBComponentInterface extends PluginInspectionInterface {

  /**
   * Return the id of the Widget.
   *
   * @return string
   */
  public function getId();

  /**
   * Return the name of the Widget.
   *
   * @return string
   */
  public function getName();

  /**
   * Return the themes of the Widget.
   *
   * @return string
   */
  public function getThemes();

  /**
   * Return the default theme of the Widget.
   *
   * @return string
   */
  public function getDefaultTheme();

  /**
   * Initialize the widget.
   *
   * @return string
   */
  public function init();

  /**
   * Build the widget form.
   *
   * @param \Drupal\dcb\Form\ComponentWizardBaseForm $componentform
   * @param array $values
   * @return float
   */
  public function build(ComponentWizardBaseForm $componentform, array $values);

  /**
   * Build the widget form.
   *
   * @param array $form_state
   * @param $items
   * @param $delta
   * @return string
   */
  public function widgetForm(&$form_state = [], $items, $delta);

  /**
   * Widget form submit.
   *
   * @param $form_state
   * @return string
   */
  public function formSubmit(&$form_state);

  /**
   * Widget form render.
   *
   * @return string
   */
  public function render();

  /**
   * Pre render widget.
   *
   * @param $values
   * @return array
   */
  public function preRender($values);

  /**
   * @param $theme
   * @return mixed
   */
  public function loadTheme($theme);

}
