<?php

namespace Drupal\dcb\Form;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dcb\Controller\DCBRegionController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheTagsInvalidator;


/**
 * Class DCBComponentRegionForm
 *
 * @package Drupal\dcb\Form
 */
class DCBComponentRegionForm extends FormBase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Cache\CacheTagsInvalidator
   */
  protected $cacheTagsInvalidator;

  /**
   * DCBComponentRegionForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager      $entityTypeManager
   * @param \Drupal\Core\Cache\CacheTagsInvalidator    $cacheTagsInvalidator
   * @param \Drupal\dcb\Controller\DCBRegionController $regionController
   */
  public function __construct(EntityTypeManager $entityTypeManager, CacheTagsInvalidator $cacheTagsInvalidator, DCBRegionController $regionController) {
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
    $this->entityTypeManager = $entityTypeManager;
    $this->regionController = $regionController;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('cache_tags.invalidator'),
      $container->get('dcb.region.controller')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dcb_component_region_order';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $regionId = NULL) {
    $form['#title'] = $this->t('Components in region');
    $form['#region'] = $regionId;
    // Get entity IDs based on region ID.
    $entityIds = $this->regionController->getRegionComponentsByWeight($regionId);
    $this->DCBComponentRegionTable($entityIds, $form);
    $form['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save order'),
    ];

    return $form;
  }

  /**
   * Builds the table portion of the form for the region administration page.
   *
   * @param $entityIds
   * @param array $form
   *   The form that is being modified, passed by reference.
   *
   * @see self::buildForm()
   */
  public function DCBComponentRegionTable($entityIds, &$form) {
    $form['table'] = [
      '#type'      => 'table',
      '#header'    => [
        t('Label'),
        t('ID'),
        t('Type'),
        t('Order'),
        t('Created')
      ],
      '#empty'     => t('There are no items yet.'),
      // TableDrag: Each array value is a list of callback arguments for
      // drupal_add_tabledrag(). The #id of the table is automatically prepended;
      // if there is none, an HTML ID is auto-generated.
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'dcbregion-order-weight',
        ],
      ],
    ];

    $delta = 10;
    // Change the delta of the weight field if have more than 20 entities.
    $count = count($entityIds);
    if ($count > 20) {
      $delta = ceil($count / 2);
    }

    // Build the table rows and columns.
    // The first nested level in the render array forms the table row with
    // #attributes and #weight.
    foreach ($entityIds as $id) {
      $entity = $this->entityTypeManager->getStorage('dcb_component')->load($id);

      // TableDrag: Mark the table row as draggable.
      $form['table'][$id]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured weight.
      $form['table'][$id]['#weight'] = $entity->get('weight')->value;
      $form['table'][$id]['#entity_id'] = $entity->id();

      // Table columns.
      $form['table'][$id]['type'] = [
        '#plain_text' => $entity->get('administrative_label')->value,
      ];
      $form['table'][$id]['id'] = [
        '#plain_text' => $entity->id(),
      ];
      $form['table'][$id]['label'] = [
        '#plain_text' => $entity->label(),
      ];
      $form['table'][$id]['weight'] = [
        '#type' => 'weight',
        '#default_value' => $entity->get('weight')->value,
        '#title' => $this->t('Weight for @title', ['@title' => $entity->label()]),
        '#title_display' => 'invisible',
        '#attributes' => [
          'class' => ['dcbregion-order-weight'],
        ],
        '#delta' => $delta,
      ];

      $form['table'][$id]['created'] = [
        '#plain_text' => \Drupal::service('date.formatter')
          ->format($entity->get('created')->value, 'date_text'),
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($form['table'] as $key => $row) {
      if (is_numeric($key)) {
        if (isset($form['table'][$key]['#entity_id'])) {
          $values = $form_state->getValue(['table', $key]);
          if (!empty($values) &&
            isset($values['weight']) &&
            $row['weight']['#default_value'] != $values['weight']) {
            $entity = $this->entityTypeManager->getStorage('dcb_component')->load($row['#entity_id']);
            $entity->set('weight', $values['weight']);
            $entity->save();
          }
        }
      }
    }
  }
}
