<?php

/**
 * @File: DCB core service.
 */

namespace Drupal\dcb\Service;

use Drupal\Core\Cache\CacheTagsInvalidator;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\dcb\DCBComponentManager;

/**
 * Class DCBCore.
 *
 * @package Drupal\dynoblock\Service
 */
class DCBCore {

  /**
   * DCB Plugin Manager.
   *
   * @var \Drupal\dcb\DCBComponentManager.
   */
  public $pluginManager;

  /**
   * DCB DB Service.
   *
   * @var \Drupal\dcb\Service\DCBDb.
   */
  public $db;

  /**
   * @var array
   */
  public $blocks = [];

  /**
   * @var array
   */
  public $themes = [];

  /**
   * @var array
   */
  public $widgets = [];

  /**
   * Core Cache Tag Invalidator
   *
   * @var CacheTagsInvalidator
   */
  public $cacheTagsInvalidator;


  /**
   * DCBCore constructor.
   *
   * @param DCBComponentManager $pluginManager
   *   Injected.
   * @param DCBDb $dcbDb
   *   Injected.
   * @param \Drupal\Core\Extension\ModuleHandler $moduleHandler
   * @param \Drupal\Core\Cache\CacheTagsInvalidator $cacheTagsInvalidator
   */
  public function __construct(DCBComponentManager $pluginManager, DCBDb $dcbDb, ModuleHandler $moduleHandler, CacheTagsInvalidator $cacheTagsInvalidator) {
    $this->pluginManager = $pluginManager;
    $this->db = $dcbDb;
    $this->moduleHandler = $moduleHandler;
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
  }

  /**
   * @param $id
   * @return mixed|null
   */
  public function getTheme($id) {
    if (empty($this->themes)) {
      $this->themes = $this->getThemes();
    }
    return array_key_exists($id, $this->themes) ? $this->themes[$id] : NULL;
  }


  /**
   * @return array|mixed
   */
  public function getThemes() {
    $themes = [];
    foreach ($this->moduleHandler->getImplementations('dcb_themes') as $module) {
      $theme = $this->moduleHandler->invoke($module, 'dcb_themes');
      foreach ($theme as &$thm) {
        $thm['full_path'] = drupal_get_path('module', $module) . '/' . $thm['path'];
        $thm['module'] = $module;
      }
      $themes += $theme;
    }
    return $this->themes = $themes;
  }

  /**
   * @param $rid
   * @param null $nid
   * @param null $label
   * @return array
   */
  public function DCBRegion($rid, $eid = NULL, $label = NULL) {
    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['dynoblock-region'],
        'data-dyno-rid' => $rid,
        'data-dyno-label' => $label,
        'data-dyno-nid' => $eid,
        'data-alacarte-id' => $rid,
        'data-alacarte-type' => 'block',
      ],
    ];
  }

  /**
   * @param $rid
   * @param array $entity
   * @return array
   */
  public function renderComponents($rid, $entity = []) {
    $blocks = $this->getBlocks($rid);
    $blocks = $this->displayBlocks($blocks, $entity);
    return [
      '#type' => 'markup',
      '#markup' => render($blocks),
    ];
  }


  /**
   * @param $id
   * @return null
   */
  public function getWidget($id) {
    $widgets = $this->loadWidgets();
    return array_key_exists($id, $widgets) ? $widgets[$id] : NULL;
  }

  /**
   * @return array|\mixed[]|null
   */
  public function loadWidgets() {
    return $this->widgets = $this->pluginManager->getDefinitions();
  }

  /**
   * @param $rid
   * @return array
   */
  public function getBlocks($rid) {
    return $this->blocks = $this->db->getBlocks($rid);
  }

  /**
   * @param $blocks
   * @param array $entity
   * @return array
   */
  public function displayBlocks($blocks, $entity = []) {
    $render = [];
    foreach ($blocks as $delta => $block) {
      $data = $block['data'];
      $id = !empty($data['widget']) ? $data['widget'] : $data['layout_id'];
      $plugin = $this->initPlugin($id);
      $widget = $this->getWidget($data['widget']);
      if ($plugin && $this->isDisplayable($data)) {
        $plugin->entity = $entity;
        $html = $plugin->init($data)->preRender($data);
        // Call theme preRender so it can modify final output.
        if (!empty($widget['parent_theme']['handler'])) {
          $theme_settings = !empty($data['global_theme_settings']) ? $data['global_theme_settings'] : [];
          $widget['parent_theme']['handler']->preRender($widget, $data, $html, $theme_settings);
        }
        $weight = isset($data['weight']) ? $data['weight'] : 0;
        $render[$delta] = [
          '#type' => 'container',
          '#weight' => $weight,
          '#attributes' => [
            'class' => ['dynoblock'],
            'data-dyno-bid' => $data['bid'],
            'data-dyno-rid' => $data['rid'],
            'data-dyno-handler' => $id,
            'data-dyno-weight' => $weight,
            'data-dyno-label' => $plugin->getName(),
            'data-alacarte-id' => 'dynoblock-' . $data['bid'],
            'data-alacarte-type' => 'block',
          ],
        ];
        if (!empty($html)) {
          $render[$delta]['content'] = [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['dynoblock-content'],
            ],
          ];
          // Render content in theme template if available.
          if ($data['theme'] && !empty($plugin->themes[$data['theme']]['template_dir'])) {
            $render[$delta]['content']['theme'] = [
              '#theme' => $data['theme'],
              '#block' => $html,
            ];
          }
          else {
            $render[$delta]['content']['dyno_block'] = $html;
          }
        }
      }
    }
    return $render;
  }

  /**
   * @param $block
   * @return bool
   */
  public function isDisplayable($block) {
    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    $is_admin = FALSE;
    if (is_array($roles) && in_array('administrator', array_values($roles))) {
      $is_admin = TRUE;
    }
    if (!empty($block['condition_token']) && !$is_admin) {
      $value = $block['condition_value'];
      $token_value = token_replace($block['condition_token']);
      switch ($block['condition_operator']) {
        case '==':
          if ($token_value == $value) {
            return TRUE;
          }
          else {

          }
          break;
        case '===':
          if ($token_value === $value) {
            return TRUE;
          }
          break;
        case '!=':
          if ($token_value != $value) {
            return TRUE;
          }
          break;
        case '!==':
          if ($token_value !== $value) {
            return TRUE;
          }
          break;
        case '<':
          if ($token_value < $value) {
            return TRUE;
          }
          break;
        case '>':
          if ($token_value > $value) {
            return TRUE;
          }
          break;
        case '<=':
          if ($token_value <= $value) {
            return TRUE;
          }
          break;
        case '>=':
          if ($token_value >= $value) {
            return TRUE;
          }
          break;
      }
    }
    else {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * @param $rid
   * @param $bid
   *
   * @return mixed
   */
  public function updateBlock($rid, $bid) {
    $result = FALSE;
    if ($block = $this->db->getBlock($rid, $bid)) {
      foreach ($_POST as $key => $value) {
        $block[$key] = $value;
      }
      $record = [
        'rid' => $rid,
        'bid' => $bid,
        'data' => serialize($block)
      ];
      $result = $this->db->update($record);
    }
    return ['result' => $result];
  }


  /**
   * @param $rid
   * @param $bid
   * @return array
   */
  public function removeBlock($rid, $bid) {
    $removed = $this->db->remove($rid, $bid);
    return ['removed' => $removed];
  }

  /**
   * @param $plugin
   * @return object
   */
  public function initPlugin($plugin) {
    if (array_key_exists($plugin, $this->loadWidgets())) {
      $plugin = $this->pluginManager->createInstance($plugin);
      $path = $this->findThemePath($plugin->getId());
      $plugin->directory = $path . $plugin->getId();
      return $plugin;
    }
  }

  /**
   * @param $widget
   * @return string
   */
  public function findThemePath($widget) {
    $widgets = $this->loadWidgets();
    if (array_key_exists($widget, $widgets)) {
      $widget = $widgets[$widget];
      $theme = $this->getTheme($widget['properties']['theme']);

      return $theme['full_path'] . '/';
    }

  }


  /**
   * Invalidates cache for a specific entity ID.
   *
   * @param $entity_type
   * @param $entity_id
   * @return string
   */
  public function invalidateCache($entity_type, $entity_id) {
    if (!empty($entity_type) && !empty($entity_id)) {
      $cache_tag = $entity_type . ':' . $entity_id;
      $this->cacheTagsInvalidator->invalidateTags([$cache_tag]);
      return "Success";
    }
    else {
      return "Failure";
    }
  }

}
