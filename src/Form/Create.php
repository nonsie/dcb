<?php

namespace Drupal\dcb\Form;

use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ctools\Wizard\FormWizardBase;

/**
 * Simple wizard step form.
 */
class Create extends ComponentWizardBaseForm {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'dcb_admin_widget_create_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current state of the form.
   * @param \Drupal\ctools\Wizard\FormWizardBase|null $wizard
   *   The base form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $formState, FormWizardBase $wizard = NULL) {
    $cached_values = $formState->getTemporaryValue('wizard');
    $core = $this->core;
    $this->initwizard($wizard, $formState);
    $this->setArgsFromCache($cached_values, $formState);
    $this->setArgsFromUri($formState);

    $eid = $formState->get('eid');
    $bid = $formState->get('bid');
    $rid = $formState->get('rid');

    if ($bid != 'new' && $formState->get('initial_load') != 'done') {
      // Load the component from the database.
      $block = $core->db->getBlock($rid, $bid);
      // Set formState storage from the database storage.
      $formState->setStorage($block['storage']);
      // Remove the storage from the array.
      unset($block['storage']);
      // Set the rest of the values to the formState.
      $formState->setValues($block);
      // Set the 'initial_load" value so this doesn't run on subsequent ajax
      // refreshes.
      $formState->set('initial_load', 'done');
      // Set the method to "edit".
      $this->method = 'edit';
    }
    elseif ($bid != 'new') {
      $this->method = 'edit';
    }
    else {
      $this->method = 'new';
    }

    if (!empty($formState->getValue('widget'))) {
      // If this "widget" value is set, we already have a good form state.
      // Init the plugin and set the rebuild value.
      $componentInstance = $core->initPlugin($formState->getValue('widget'));
      $componentInstance->rebuild = TRUE;
      $componentInstance->form = [];
      $formState->set('widget', $formState->getValue('widget'));
    }
    elseif (!empty($formState->get('widget'))) {
      $componentInstance = $core->initPlugin($formState->get('widget'));
      $componentInstance->rebuild = TRUE;
    }
    elseif (isset($cached_values['selected_component'])) {
      // This is a new first time load, use the value from the wizard.
      $componentInstance = $core->initPlugin($cached_values['selected_component']);
      $formState->set('widget', $cached_values['selected_component']);
    }

    // ksm($formState->getValues());
    // Add the Component instance as a property of this form for easy access.
    $this->setComponentInstance($componentInstance);

    // Initialize the component edit form.
    $this->componentInstance->init()->getOuterForm($this, $formState->getValues());

    // Build the pieces of the form.
    $this->buildWidgetForm($formState);
    $this->buildThemeSelection($formState);
    $this->buildParentThemeSettings($formState);
    $this->addDefaultFields($eid);
    $this->addExtraSettings($formState->getValues());

    $returnvalue = $this->componentInstance->form;

    /*
     * If $this gets too complicated, it tends to cause ajax errors
     * and crash the ajax responses. Remove the things that are not necessary
     * for actually rendering the form.
     */
    unset($this->formState);
    unset($this->componentInstance);

    return $returnvalue;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $formState) {
    // Prepare some variables for easier use further down.
    $bid = $formState->get('bid');
    $rid = $formState->get('rid');
    $eid = $formState->get('eid');
    $etype = $formState->get('etype');
    $weight = $formState->getValue('weight');
    $conditions['condition_token'] = $formState->getValue('condition_token');
    $conditions['condition_value'] = $formState->getValue('value');
    $conditions['condition_operators'] = $formState->getValue('operators');

    // If this is a new block, generate the bid and set it to the formState.
    if ($bid == 'new') {
      $method = 'new';
      $bid = time();
      $formState->set('bid', $bid);
    }
    else {
      $method = 'edit';
    }

    // Set some arbitrary form state values to the "values" store.
    $formState->setValue('bid', $bid);
    $formState->setValue('rid', $rid);
    $formState->setValue('nid', $eid);

    // Get only the good stuff from the formState.
    // @See Drupal\Core\Form\FormStateInterface.
    $formState->cleanValues();

    // Add the storage to the array as well.
    $prepared_values = $formState->getValues();
    $prepared_values['storage'] = $formState->getStorage();

    // Prepare the data for saving.
    $record = [
      'rid' => $rid,
      'bid' => $bid,
      'data' => serialize($prepared_values),
      'conditions' => serialize($conditions),
      'weight' => $weight,
    ];

    // Take the submitted data and return an AJAX command to update the page.
    $renderRecord = [
      'rid' => $rid,
      'bid' => $bid,
      'data' => $formState->getValues(),
      'conditions' => $conditions,
      'weight' => $weight,
    ];

    // Run the formsubmit() function on the widget, if there is one.
    $componentInstance = $this->core->initPlugin($formState->getValue('widget'));
    $componentInstance->formSubmit($formState);

    // Get render array of the new or updated component.
    $block = $this->core->displayBlocks([$renderRecord]);

    // If this is new, append to the page.
    if ($method == 'new') {
      // Save the record.
      $this->core->db->save($record);
      $command = new AppendCommand('div.dynoblock-region[data-dyno-rid="' . $record['rid'] . '"]', $block);
      // Place the ajax command on the form state so it can be processed by
      // the wizard.
      $formState->setValue('ajaxcommand', $command);
    }

    // If this is an edit, replace the current component on the page.
    if ($method == 'edit') {
      // Update the record.
      $this->core->db->update($record);
      $command = new ReplaceCommand('div[data-dyno-bid="' . $bid . '"]', $block);
      // Place the ajax command on the form state so it can be processed by
      // the wizard.
      $formState->setValue('ajaxcommand', $command);
    }

    // Clear the entity cache tag for this entity.
    $this->core->invalidateCache($etype, $eid);
  }

}
