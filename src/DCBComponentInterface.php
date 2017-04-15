<?php

namespace Drupal\dcb;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\dcb\Form\ComponentWizardBaseForm;

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
   * @return float
   */
  public function build(ComponentWizardBaseForm $componentform, array $values);

  /**
   * Build the widget form.
   *
   * @return string
   */
  public function widgetForm(&$form_state = array(), $items, $delta);

  /**
   * Widget form submit.
   *
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
   * @return array
   */
  public function preRender($values);

  /**
   * @param $theme
   * @return mixed
   */
  public function loadTheme($theme);

}
