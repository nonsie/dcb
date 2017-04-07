<?php

namespace Drupal\dynoblock\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dynoblock\Service\DynoblockCore;
use \Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Simple wizard step form.
 */
class SelectGroup extends FormBase {


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

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'dynoblock_admin_group_select_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    $themes = $this->core->getThemes();
    $selected_theme =  $cached_values['theme']['id'];

    foreach ($themes as $theme) {
      $options[$theme['id']] = $theme['label'] . ' - ' . $theme['description_short'];
    }

    $form['theme'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Select Component Group'),
      '#default_value' => $selected_theme,
      '#options' => $options,
    );

    $form['rid'] = [
      '#title' => $this->t('rid'),
      '#type' => 'item',
      '#markup' => (!empty($cached_values['rid'])) ? $cached_values['rid'] : '',
    ];

    return $form;
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
    $themes = $this->core->getThemes();
    $cached_values = $form_state->getTemporaryValue('wizard');
    $cached_values['theme'] =  $themes[$form_state->getValue('theme')];
    $form_state->setTemporaryValue('wizard', $cached_values);
  }

}
