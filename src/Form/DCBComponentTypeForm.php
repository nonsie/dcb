<?php

namespace Drupal\dcb\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DCBComponentTypeForm.
 */
class DCBComponentTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $dcb_component_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $dcb_component_type->label(),
      '#description' => $this->t("Label for the DCB Component type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $dcb_component_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\dcb\Entity\DCBComponentType::load',
      ],
      '#disabled' => !$dcb_component_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $dcb_component_type = $this->entity;
    $status = $dcb_component_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label DCB Component type.', [
          '%label' => $dcb_component_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label DCB Component type.', [
          '%label' => $dcb_component_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($dcb_component_type->toUrl('collection'));
  }

}
