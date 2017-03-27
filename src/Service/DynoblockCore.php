<?php

namespace Drupal\dynoblock\Service;

use Drupal\Core\Extension\ModuleHandler;
use Drupal\dynoblock\DynoblockManager;

/**
 * Class DynoblockCore.
 *
 * @package Drupal\dynoblock\Service
 */
class DynoblockCore {

  /**
   * Dynoblock Plugin Manager.
   *
   * @var \Drupal\dynoblock\DynoblockManager.
   */
  public $pluginManager;

  /**
   * Dynoblock DB Serive.
   *
   * @var \Drupal\dynoblock\Service\DynoblockDb.
   */
  public $db;

  /**
   * @var array
   */
  public $blocks = array();

  /**
   * @var array
   */
  public $themes = array();

  /**
   * @var array
   */
  public $widgets = array();

  /**
   * DynoblockCore constructor.
   *
   * @param DynoblockManager $pluginManager
   *   Injected.
   * @param DynoblockDb $dynoblockDb
   *   Injected.
   */
  public function __construct(DynoblockManager $pluginManager, DynoblockDb $dynoblockDb, ModuleHandler $moduleHandler) {
    $this->pluginManager = $pluginManager;
    $this->db = $dynoblockDb;
    $this->moduleHandler = $moduleHandler;
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
   * @return array|mixed|void
   */
  public function getThemes() {
    $themes = array();
    foreach ($this->moduleHandler->getImplementations('dynoblock_themes') as $module) {
      $theme = $this->moduleHandler->invoke($module, 'dynoblock_themes');
      foreach ($theme as &$thm) {
        $thm['full_path'] = drupal_get_path('module', $module) . '/' . $thm['path'];
        $thm['module'] = $module;
      }
      $themes += $theme;
    }
    return $this->themes = $themes;
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
  public function displayBlocks($blocks, $entity = array()) {
    $render = array();
    foreach ($blocks as $delta => $block) {
      $data = $block['data'];
      $id = !empty($data['widget']) ? $data['widget'] : $data['layout_id'];
      $plugin = _dynoblock_find_layout_handler($id);
      $widget = $this->getWidget($data['widget']);
      if ($plugin && $this->isDisplayable($data)) {
        $path = _dynoblock_find_layout_path($id);
        $plugin->directory = $path;
        $plugin->entity = $entity;
        $html = $plugin->init($data)->preRender($data);
        // Call theme preRender so it can modify final output.
        if (!empty($widget['parent_theme']['handler'])) {
          $theme_settings = !empty($data['global_theme_settings']) ? $data['global_theme_settings'] : array();
          $widget['parent_theme']['handler']->preRender($widget, $data, $html, $theme_settings);
        }
        $weight = isset($data['weight']) ? $data['weight'] : 0;
        $render[$delta] = array(
          '#type' => 'container',
          '#weight' => $weight,
          '#attributes' => array(
            'class' => array('dynoblock'),
            'data-dyno-bid' => $data['bid'],
            'data-dyno-rid' => $data['rid'],
            'data-dyno-handler' => $id,
            'data-dyno-weight' => $weight,
            'data-dyno-label' => $widget['label'],
            'data-alacarte-id' => 'dynoblock-' . $data['bid'],
            'data-alacarte-type' => 'block',
          ),
        );
        if (!empty($html)) {
          $render[$delta]['content'] = array(
            '#type' => 'container',
            '#attributes' => array(
              'class' => array('dynoblock-content'),
            ),
          );
          $render[$delta]['content']['dyno_block'] = $html;
        }
      }
    }
    return $render;
  }

  /**
   * @param $block
   * @param $html
   * @return mixed|null
   */
  public function renderNewBlock($block, $html) {
    $render['container'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('dynoblock'),
        'data-dyno-bid' => $block['bid'],
        'data-dyno-rid' => $block['rid'],
        'data-dyno-handler' => $block['widget'],
        'data-alacarte-id' => 'dynoblock-' . $block['bid'],
        'data-alacarte-type' => 'block',
      ),
    );
    $render['container']['content'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('dynoblock-content'),
      ),
    );
    $render['container']['content']['block'] = array(
      '#type' => 'markup',
      '#markup' => $html,
    );
    return render($render);
  }

  /**
   * @param $html
   * @return mixed|null
   */
  public function wrapEditBlock($html) {
    $wrapper = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('dynoblock-content'),
      ),
    );
    $wrapper[] = array(
      '#type' => 'markup',
      '#markup' => $html,
    );
    return render($wrapper);
  }

  /**
   * @param $rid
   * @param null $nid
   * @param null $label
   * @return array
   */
  public function dynoRegion($rid, $nid = NULL, $label = NULL) {
    return array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('dynoblock-region'),
        'data-dyno-rid' => $rid,
        'data-dyno-label' => $label,
        'data-dyno-nid' => $nid,
        'data-alacarte-id' => $rid,
        'data-alacarte-type' => 'block',
      ),
    );
  }

  /**
   * @param $rid
   * @param array $entity
   * @return array
   */
  public function renderDynoBlocks($rid, $entity = array()) {
    $blocks = $this->getBlocks($rid);
    $blocks = $this->displayBlocks($blocks, $entity);
    return array(
      '#type' => 'markup',
      '#markup' => render($blocks),
    );
  }

  /**
   * @param $block
   * @return bool
   */
  public function isDisplayable($block) {
    global $user;
    $is_admin = FALSE;
    if (is_array($user->roles) && in_array('administrator', array_values($user->roles))) {
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
          if ($token_value  < $value) {
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

}
