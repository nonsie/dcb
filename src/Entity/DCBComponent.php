<?php

namespace Drupal\dcb\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the DCB Component entity.
 *
 * @ingroup dcb
 *
 * @ContentEntityType(
 *   id = "dcb_component",
 *   label = @Translation("DCB Component"),
 *   bundle_label = @Translation("DCB Component Type"),
 *   handlers = {
 *     "storage" = "Drupal\dcb\Storage\DCBComponentStorage",
 *     "view_builder" = "Drupal\dcb\Builder\DCBComponentViewBuilder",
 *     "list_builder" = "Drupal\dcb\Builder\DCBComponentListBuilder",
 *     "views_data" = "Drupal\dcb\Entity\DCBComponentViewsData",
 *     "translation" = "Drupal\dcb\Handler\DCBComponentTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\dcb\Form\DCBComponentForm",
 *       "add" = "Drupal\dcb\Form\DCBComponentForm",
 *       "edit" = "Drupal\dcb\Form\DCBComponentForm",
 *       "delete" = "Drupal\dcb\Form\DCBComponentDeleteForm",
 *     },
 *     "access" = "Drupal\dcb\Handler\DCBComponentAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\dcb\Provider\DCBComponentHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "dcb_component",
 *   fieldable = FALSE,
 *   data_table = "dcb_component_field_data",
 *   revision_table = "dcb_component_revision",
 *   revision_data_table = "dcb_component_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer dcb component entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "type",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/content/dcb_component/{dcb_component}",
 *     "add-page" = "/admin/content/dcb_component/add/{region_id}",
 *     "add-form" = "/admin/content/dcb_component/add/{dcb_component_type}/{region_id}",
 *     "edit-form" = "/admin/content/dcb_component/{dcb_component}/edit",
 *     "delete-form" = "/admin/content/dcb_component/{dcb_component}/delete",
 *     "version-history" = "/admin/content/dcb_component/{dcb_component}/revisions",
 *     "revision" = "/admin/content/dcb_component/{dcb_component}/revisions/{dcb_component_revision}/view",
 *     "revision_revert" = "/admin/content/dcb_component/{dcb_component}/revisions/{dcb_component_revision}/revert",
 *     "revision_delete" = "/admin/content/dcb_component/{dcb_component}/revisions/{dcb_component_revision}/delete",
 *     "translation_revert" = "/admin/content/dcb_component/{dcb_component}/revisions/{dcb_component_revision}/revert/{langcode}",
 *     "collection" = "/admin/content/dcb_component",
 *   },
 *   bundle_entity_type = "dcb_component_type",
 *   field_ui_base_route = "entity.dcb_component_type.edit_form"
 * )
 */
class DCBComponent extends RevisionableContentEntityBase implements DCBComponentInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);

    $values += [
      'user_id' => \Drupal::currentUser()->id(),
      'region_id' => $values['region_id'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the dcb_component owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getRegion() {
    return $this->get('region_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRegion($regionId) {
    $this->set('region_id', $regionId);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->get('weight')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->set('weight', $weight);
    return $this;
  }

  /**
   * @return \Drupal\Core\Field\FieldItemListInterface
   */
  public function getAdministrativeLabel() {
    return $this->get('administrative_label');
  }

  /**
   * @param $adminLabel
   *
   * @return $this
   */
  public function setAdministrativeLabel($adminLabel) {
    $this->set('administrative_label', $adminLabel);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
  * {@inheritdoc}
  */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);
    if ($rel === 'add-form' && ($this->getEntityType()->hasKey('bundle'))) {
      $parameter_name = $this->getEntityType()->getBundleEntityType() ?: $this->getEntityType()->getKey('bundle');
      $uri_route_parameters[$parameter_name] = $this->bundle();
      $uri_route_parameters['region_id'] = 'none';
    }
    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }


    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the DCB Component entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
        'region' => 'hidden',
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
        'region' => 'hidden',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['administrative_label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Administrative Label'))
      ->setDescription(t('A short label for this component. For administrative purposes only.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'max_length' => 60,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
        'region' => 'hidden',
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the DCB Component is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    $fields['region_id'] = BaseFieldDefinition::create('entity_reference_revisions')
    ->setLabel(t('DCB Region ID'))
    ->setDescription(t('The Region in which this component is assigned.'))
    ->setCardinality(1)
    ->setRequired(TRUE)
    ->setSetting('target_type', 'dcb')
    ->setDisplayConfigurable('form', TRUE)
    ->setDisplayConfigurable('view', TRUE);


    $fields['view_mode'] = BaseFieldDefinition::create('string')
      ->setLabel(t('View Mode'))
      ->setDescription(t('The View Mode to use for this component'));

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDescription(t('The weight of this component in the region its assigned.'))
      ->setDefaultValue(0);

    return $fields;
  }

}
