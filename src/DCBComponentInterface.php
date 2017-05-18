<?php

namespace Drupal\dcb;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dcb\Form\ComponentWizardBaseForm;

/**
 * Interface DCBComponentInterface.
 *
 * @package Drupal\dcb
 */
interface DCBComponentInterface extends PluginInspectionInterface {

  /**
   * Return the id of the Widget.
   *
   * @return string
   *   The ID of this component plugin.
   */
  public function getId();

  /**
   * Return the name of the component.
   *
   * @return string
   *   The name of the component plugin.
   */
  public function getName();

  /**
   * Return the themes of the component.
   *
   * Example format:
   * themes[
   *     "dcb-cards-default" => [
   *        "label" = "Default",
   *        "handler" = "CardsDefaultTheme",
   *        "preview_image" = "cards.png",
   *     ],
   *      "another-theme" => ....
   *   ];
   *
   * @return array
   *   An array of available themes.
   */
  public function getThemes();

  /**
   * Return the default theme of the Widget.
   *
   * @return string
   *   The machine name of the theme.
   */
  public function getDefaultTheme();

  /**
   * Initialize the widget.
   *
   * Allows for implementing plugins to make modifications before returning.
   *
   * @return \Drupal\dcb\DCBComponentInterface
   *   Returns itself ($this).
   */
  public function init();

  /**
   * Build the outer admin form.
   *
   * This method should append the "fields" key to the administrative form.
   * The outer array should be a container with the key of "fields"
   *
   * @param \Drupal\dcb\Form\ComponentWizardBaseForm $componentform
   *   The administrative form for the component.
   * @param array $values
   *   Array of the administrative form's values.
   *
   * @see \Drupal\dcb\Plugin\DCBComponent\DCBComponentBase::getOuterForm()
   */
  public function getOuterForm(ComponentWizardBaseForm $componentform, array $values);

  /**
   * Build the repeating form elements.
   *
   * Builds the repeating admin form elements for the component.
   *
   * @param array $form_state
   *   The Drupal form_state.
   * @param $items
   *   The form values for the specific fieldset.
   * @param $delta
   *   The delta of the item being created
   *
   * @return array
   *   Form array of one of the repeating items to be added to the admin form.
   *
   */
  public function getRepeatingFields(&$form_state = [], $items, $delta);

  /**
   * Component form submit handler.
   *
   * This method can be implemented by a component plugin when something needs
   * to be done when the admin form is submitted.
   *
   * @param FormStateInterface $form_state
   *   The Drupal form_state.
   *
   */
  public function formSubmit(FormStateInterface $form_state);

  /**
   * Pre render the component.
   *
   * Takes the raw form state values and passes them to the correct handlers
   * for processing before being passed to the Drupal theme layer.
   *
   * Calls preRender on each DCB field that this component implements then
   * passes the values to the specified DCB theme class for final modifications.
   *
   * @param $form_values
   *   The raw values from the admin form.
   *
   * @return array
   *   The final array that will be passed to the specified twig template.
   */
  public function preRender($form_values);

  /**
   * Loads and returns an instance of the requested DCB theme class.
   *
   * @param $theme
   *   The requested DCB theme.
   *
   * @return \Drupal\dcb\DCBComponentTheme
   *   The theme object.
   */
  public function loadTheme($theme);

  /**
   * @param $values
   * @return mixed
   */
  public function outerForm($values);

  /**
   * @param array $values
   * @param $delta
   * @return mixed
   */
  public function repeatingFields($values = [], $delta);

  /**
   * @param $pluginId
   * @param $theme
   * @return mixed
   */
  public function initTheme($pluginId, $theme);

  /**
   * @param $options
   * @return mixed
   */
  public function registerItemThemeOptions($options);

  /**
   * @param $field_info
   * @return mixed
   */
  public function registerInnerFieldOptions($field_info);

  /**
   * @param $field_info
   * @return mixed
   */
  public function registerOuterFieldOptions($field_info);

  /**
   * @param $id
   * @param bool $init
   * @param array $form_state
   * @return mixed
   */
  public function getField($id, bool $init = FALSE, FormStateInterface $form_state = NULL);

  /**
   * @param $field
   * @return mixed
   */
  public function initField($field);

  /**
   * @return mixed
   */
  public function loadFields();

  /**
   * @return mixed
   */
  public function getShortDesc();

  /**
   * @return mixed
   */
  public function getClass();

  /**
   * @return mixed
   */
  public function getNamespace();

  /**
   * @return mixed
   */
  public function ajaxsubmit();

}
