<?php

namespace Drupal\dcb\Form;

use Drupal\Component\Utility\UrlHelper;
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
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   * @param \Drupal\ctools\Wizard\FormWizardBase|NULL $wizard
   */
  public function buildForm(array $form, FormStateInterface $formState, FormWizardBase $wizard = NULL) {
    $cached_values = $formState->getTemporaryValue('wizard');

    // Determine if this is a current component or a new instance
    if (isset($cached_values['selected_component']) && !empty($cached_values['selected_component'])) {
      $component_type = $cached_values['selected_component'];
    }
    else {
      $component_type = '';
    }

    $this->setArgsFromCache($cached_values, $formState);
    $this->setArgsFromUri($formState);

    $eid = $formState->get('eid');
    $bid = $formState->get('bid');
    $rid = $formState->get('rid');

    $form = $this->core->getComponentAdminForm($rid, $bid, $eid, $component_type);

    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public function submitForm(array &$form, FormStateInterface $formState) {
    $component_storage = $this->core->saveComponentStorageArray($formState, $formState->getValue(['meta','component']));

    // Prepare the rendered component for ajax update.
    $prepared = $this->core->renderComponent($component_storage['meta']['rid'], $component_storage['meta']['bid'], 'drupal_theme_renderer');
    $rendered = $this->renderer->render($prepared);

    if ($formState->getValue(['meta','bid']) === 'new') {
      $command = new AppendCommand('div.dcb-region[data-dcb-rid="' . $component_storage['meta']['rid'] . '"]', $rendered);
      $formState->setValue('ajaxcommand', $command);
    }
    else {
      $command = new ReplaceCommand('div[data-dcb-bid="' . $component_storage['meta']['bid'] . '"]', $rendered);
      $formState->setValue('ajaxcommand', $command);
    }

    // Invalidate the cache for this node so the content appears on next refresh.
    $this->core->invalidateCache('node', $component_storage['meta']['eid']);
  }

  /**
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  protected function setArgsFromUri(FormStateInterface $formState) {
    $args = UrlHelper::parse($this->request->getCurrentRequest()->getUri())['query'];
    $expected_args = ['rid', 'bid', 'etype', 'eid'];
    foreach ($expected_args as $arg) {
      if (isset($args[$arg])) {
        $formState->set($arg, $args[$arg]);
      }
    }
  }

  /**
   * @param $cached_values
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  protected function setArgsFromCache($cached_values, FormStateInterface $formState) {
    $expected_args = ['rid', 'bid', 'etype', 'eid'];
    foreach ($expected_args as $arg) {
      if (isset($cached_values[$arg])) {
        $formState->set($arg, $cached_values[$arg]);
      }
    }
  }

}
