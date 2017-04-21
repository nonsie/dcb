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
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @param \Drupal\ctools\Wizard\FormWizardBase|null $wizard
   * @return array The form structure.
   * The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, FormWizardBase $wizard = NULL) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    $core = $this->core;
    $this->initwizard($wizard, $form_state);
    $this->setArgsFromCache($cached_values, $form_state);
    $this->setArgsFromURI($form_state);

    $eid = $form_state->get('eid');
    $bid = $form_state->get('bid');
    $rid = $form_state->get('rid');

    if ($bid != 'new' && $form_state->get('initial_load') != 'done') {
      // Load the component from the database.
      $block = $core->db->getBlock($rid, $bid);
      // Set form_state storage from the database storage.
      $form_state->setStorage($block['storage']);
      // Remove the storage from the array.
      unset($block['storage']);
      // Set the rest of the values to the form_state
      $form_state->setValues($block);
      // Set the 'initial_load" value so this doesn't run on subsequent ajax refreshes.
      $form_state->set('initial_load', 'done');
      // Set the method to "edit"
      $this->method = 'edit';
    }
    elseif ($bid != 'new') {
      $this->method = 'edit';
    }
    else {
      $this->method = 'new';
    }


    if (!empty($form_state->getValue('widget'))) {
      // If this "widget" value is set, we already have a good form state.
      // Init the plugin and set the rebuild value.
      $componentInstance = $core->initPlugin($form_state->getValue('widget'));
      $componentInstance->rebuild = TRUE;
      $componentInstance->form = [];
      $form_state->set('widget', $form_state->getValue('widget'));
    }
    elseif(isset($cached_values['selected_component'])) {
      // This is a new first time load, use the value from the wizard.
      $componentInstance = $core->initPlugin($cached_values['selected_component']);
      $form_state->set('widget', $cached_values['selected_component']);
    }
    elseif(!empty($form_state->get('widget'))) {
      $componentInstance = $core->initPlugin($form_state->get('widget'));
    }

    //ksm($form_state->getValues());

    // Add the Component instance as a property of this form for easy access.
    $this->setComponentInstance($componentInstance);

    // Initialize the component edit form.
    $this->componentInstance->init()->build($this, $form_state->getValues());

    // Build the pieces of the form
    $this->buildWidgetForm($form_state);
    $this->buildThemeSelection($form_state);
    $this->buildParentThemeSettings($form_state);
    $this->addDefaultFields($eid);
    $this->addExtraSettings($form_state->getValues());

    $returnvalue = $this->componentInstance->form;

    /**
     * if $this gets too complicated, it tends to cause ajax errors
     * and crash the ajax responses. Remove the things that are not necessary for
     * actually rendering the form.
     */
    unset($this->form_state);
    unset($this->componentInstance);


    return $returnvalue;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Prepare some variables for easier use further down.
    $bid = $form_state->get('bid');
    $rid = $form_state->get('rid');
    $eid = $form_state->get('eid');
    $etype = $form_state->get('etype');
    $weight = $form_state->getValue('weight');
    $conditions['condition_token'] = $form_state->getValue('condition_token');
    $conditions['condition_value'] = $form_state->getValue('value');
    $conditions['condition_operators'] = $form_state->getValue('operators');

    // If this is a new block, generate the bid and set it to the form_state.
    if ($bid == 'new') {
      $method = 'new';
      $bid = time();
      $form_state->set('bid', $bid);
    }
    else {
      $method = 'edit';
    }

    // Set some arbitrary form state values to the "values" store.
    $form_state->setValue('bid', $bid);
    $form_state->setValue('rid', $rid);
    $form_state->setValue('nid', $eid);

    // Get only the good stuff from the form_state. @See Drupal\Core\Form\FormStateInterface
    $form_state->cleanValues();

    // Add the storage to the array as well.
    $prepared_values = $form_state->getValues();
    $prepared_values['storage'] = $form_state->getStorage();

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
      'data' => $form_state->getValues(),
      'conditions' => $conditions,
      'weight' => $weight,
    ];

    // run the formsubmit() function on the widget, if there is one
    $componentInstance = $this->core->initPlugin($form_state->getValue('widget'));
    $componentInstance->formSubmit($form_state);

    // Get render array of the new or updated component.
    $block = $this->core->displayBlocks([$renderRecord]);

    // If this is new, append to the page.
    if ($method == 'new') {
      // Save the record.
      $this->core->db->save($record);
      $command = new AppendCommand('div.dynoblock-region[data-dyno-rid="' . $record['rid'] . '"]', $block);
      // Place the ajax command on the form state so it can be processed by the wizard.
      $form_state->setValue('ajaxcommand', $command);
    }

    // If this is an edit, replace the current component on the page.
    if ($method == 'edit') {
      // Update the record.
      $this->core->db->update($record);
      $command = new ReplaceCommand('div[data-dyno-bid="' . $bid . '"]', $block);
      // Place the ajax command on the form state so it can be processed by the wizard.
      $form_state->setValue('ajaxcommand', $command);
    }

    // Clear the entity cache tag for this entity.
    $this->core->invalidateCache($etype, $eid);
  }

}
