<?php

namespace Drupal\dcb\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Cache\CacheTagsInvalidator;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RedirectDestination;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for DCB Component edit forms.
 *
 * @ingroup dcb
 */
class DCBComponentForm extends ContentEntityForm {

  /**
   * @var \Drupal\Core\Cache\CacheTagsInvalidator
   */
  private $cacheTagsInvalidator;

  public function __construct(EntityManagerInterface $entity_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, CacheTagsInvalidator $cacheTagsInvalidator, RedirectDestination $redirectDestination) {
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
    $this->redirectDestination = $redirectDestination;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('cache_tags.invalidator'),
      $container->get('redirect.destination')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    if (!$this->entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 10,
      ];
    }

    $view_modes_storage = $this->entityTypeManager->getStorage('dcb_component_type')->load($this->entity->bundle())->get('view_modes');
    $all_view_modes = $this->entityManager->getViewModeOptionsByBundle('dcb_component', $this->entity->bundle());
    foreach($view_modes_storage as $key => $value) {
      if ($value !== '0') {
        $selected_view_modes[$key] = $all_view_modes[$key];
      }
    }

    if(!empty($selected_view_modes)) {
      $form['view_mode_select'] = [
        '#type' => 'select',
        '#title' => "choose a view mode",
        '#options' => $selected_view_modes,
        '#default_value' => $this->entity->get('view_mode')->getString(),
      ];
    }

    $form_actions = $form['actions'];

    unset($form['actions']);

    $form = [
      'ajax_wrap' => [
        '#type' => 'container',
        '#prefix' => '<div id="dcbcomponent-entity-form">',
        '#suffix' => '</div>',
        'messages' => [
          '#type' => 'status_messages',
        ],
        'content' => $form,
      ],
      'actions' => $form_actions,
    ];

    unset($form['actions']['delete']);

    $form['actions']['submit']['#ajax'] = [
      'callback' => '::submitAjax',
      'wrapper' => 'dcbcomponent-entity-form',
    ];

    return $form;
  }

  public function submitAjax($form, FormStateInterface $formState) {
    $response = new AjaxResponse();

    if ($formState::hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#dcbcomponent-entity-form', $form['ajax_wrap']));
    }
    else {
      $url = Url::fromUserInput("/" . $this->redirectDestination->get())->setAbsolute()->toString();
      $response->addCommand(new RedirectCommand($url));
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime(REQUEST_TIME);
      $entity->setRevisionUserId(\Drupal::currentUser()->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    $entity->set('view_mode', $form_state->getValue('view_mode_select'));

    $status = parent::save($form, $form_state);

    $this->cacheTagsInvalidator->invalidateTags(['dcbregion:' . $entity->get('region_id')->getString()]);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label DCB Component.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label DCB Component.', [
          '%label' => $entity->label(),
        ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityFromRouteMatch(RouteMatchInterface $route_match, $entity_type_id) {
    if ($route_match->getRawParameter($entity_type_id) !== NULL) {
      $entity = $route_match->getParameter($entity_type_id);
    }
    else {
      $values = [];
      // If the entity has bundles, fetch it from the route match.
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      if ($bundle_key = $entity_type->getKey('bundle')) {
        if (($bundle_entity_type_id = $entity_type->getBundleEntityType()) && $route_match->getRawParameter($bundle_entity_type_id)) {
          $values[$bundle_key] = $route_match->getParameter($bundle_entity_type_id)->id();
        }
        elseif ($route_match->getRawParameter($bundle_key)) {
          $values[$bundle_key] = $route_match->getParameter($bundle_key);
        }
      }
      $values['region_id'] = $route_match->getParameter('region_id');
      $entity = $this->entityTypeManager->getStorage($entity_type_id)->create($values);
    }

    return $entity;
  }

}
