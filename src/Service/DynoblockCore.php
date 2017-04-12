<?php

namespace Drupal\dynoblock\Service;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Cache\CacheTagsInvalidator;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dynoblock\DynoBlockForms;
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
   * Core Cache Tag Invalidator
   *
   * @var CacheTagsInvalidator
   */
  public $cacheTagsInvalidator;


  /**
   * DynoblockCore constructor.
   *
   * @param DynoblockManager $pluginManager
   *   Injected.
   * @param DynoblockDb $dynoblockDb
   *   Injected.
   */
  public function __construct(DynoblockManager $pluginManager, DynoblockDb $dynoblockDb, ModuleHandler $moduleHandler, CacheTagsInvalidator $cacheTagsInvalidator) {
    $this->pluginManager = $pluginManager;
    $this->db = $dynoblockDb;
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
      $plugin = $this->initPlugin($id);
      $widget = $this->getWidget($data['widget']);
      if ($plugin && $this->isDisplayable($data)) {
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
            'data-dyno-label' => $plugin->getName(),
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
          // Render content in theme template if available.
          if ($data['theme'] && !empty($plugin->themes[$data['theme']]['template_dir'])) {
            $render[$delta]['content']['theme'] = array(
              '#theme' => $data['theme'],
              '#block' => $html,
            );
          } else {
            $render[$delta]['content']['dyno_block'] = $html;
          }
        }
      }
    }
    return $render;
  }

  /**
   * @param $output
   * @param $plugin
   * @param $block
   * @return mixed|null
   */
  public function renderNewBlock($output, $plugin, $block) {
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
    if ($block['theme'] && !empty($plugin->themes[$block['theme']]['template_dir'])) {
      $render['container']['content']['block'] = array(
        '#theme' => $block['theme'],
        '#block' => $output,
      );
    } else {
      $render['container']['content']['block'] = [
        '#type' => 'markup',
        '#markup' => render($output),
      ];
    }
    return render($render);
  }

  /**
   * @param $output
   * @param $plugin
   * @param $data
   * @return mixed|null
   */
  public function wrapEditBlock($output, $plugin, $data) {
    $wrapper = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('dynoblock-content'),
      ),
    );
    if ($data['theme'] && !empty($plugin->themes[$data['theme']]['template_dir'])) {
      $wrapper[] = array(
        '#theme' => $data['theme'],
        '#block' => $output,
      );
    } else {
      $wrapper[] = [
        '#type' => 'markup',
        '#markup' => render($output),
      ];
    }
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
      $record = array(
        'rid' => $rid,
        'bid' => $bid,
        'data' => serialize($block)
      );
      $result = $this->db->update($record);
    }
    return ['result' => $result];
  }

  /**
   * @param $method
   * @return array
   */
  public function saveBlock($method) {
    // Check that this is either an edit or new save.
    // If new save, make sure bid does not already exist.
    $output = array('saved' => FALSE);
    if (!empty($_POST['rid'])
      && !empty($_POST['bid'])
      && ($method == 'edit'
        || ($method == 'new' && !$this->db->getBlock($_POST['rid'], $_POST['bid'])))) {
      $form = $this->initPlugin(!empty($_POST['widget']) ? $_POST['widget'] : NULL);
      if ($form) {
        $form->id = $_POST['widget'];
        $form->formSubmit($_POST);
        $record = array(
          "rid" => $_POST['rid'],
          "bid" => $_POST['bid'],
          "data" => serialize($_POST),
          'weight' => NULL,
          'conditions' => serialize(array(
            'condition_token' => !empty($_POST['condition_token']) ? !empty($_POST['condition_token']) : NULL,
            'condition_operator' => !empty($_POST['condition_operator']) ? !empty($_POST['condition_operator']) : NULL,
            'condition_value' => !empty($_POST['condition_value']) ? !empty($_POST['condition_value']) : NULL,
          ))
        );
        if ($method == 'edit') {
          $action = $this->db->update($record);
        }
        else {
          $action = $this->db->save($record);
        }
        if ($action) {
          $layout = $this->initPlugin($_POST['widget']);
          if ($layout) {
            $output = $layout->init($_POST)->preRender($_POST);
            // Call theme preRender so it can modify final output.
            $widget = $this->getWidget($_POST['widget']);
            if (!empty($widget['parent_theme']['handler'])) {
              $theme_settings = !empty($_POST['global_theme_settings']) ? $_POST['global_theme_settings'] : array();
              $widget['parent_theme']['handler']->preRender($widget, $_POST, $output, $theme_settings);
            }
            if ($method == 'new') {
              $output = $this->renderNewBlock($output, $form, $_POST);
            }
            else {
              $output = $this->wrapEditBlock($output, $form, $_POST);
            }
            $output = array(
              'saved' => TRUE,
              'bid' => $_POST['bid'],
              'rid' => $_POST['rid'],
              'handler' => $_POST['widget'],
              'block' => $output,
            );
          }
        }
      }
    }
    return $output;
  }

  /**
   * @param $rid
   * @param $bid
   * @return array
   */
  public function removeBlock($rid, $bid) {
    $removed = $this->db->remove($rid, $bid);
    return array('removed' => $removed);
  }

  /**
   * @param $rid
   * @param $bid
   * @param $nid
   * @return mixed
   */
  public function editBlock($rid, $bid, $nid) {
    return DynoBlockForms::editForm($rid, $bid, $nid);
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
   * @param $data
   * @return \Drupal\Core\Ajax\CommandInterface[]
   */
  public function getAjaxCommands($data) {
    $response = new AjaxResponse();
    $replace = new ReplaceCommand(NULL, $data);
    $response->addCommand($replace);
    $attachments_processor = \Drupal::service('ajax_response.attachments_processor');
    $attachments_processor->processAttachments($response);
    $commands = $response->getCommands();
    return $commands;
  }


  /**
   * Invalidates cache for a specific entity ID.
   *
   * @param $entity_type
   * @param $entity_id
   */
  public function invalidateCache($entity_type, $entity_id) {
    if(!empty($entity_type) && !empty($entity_id)) {
      $cache_tag = $entity_type . ':' . $entity_id;
      $this->cacheTagsInvalidator->invalidateTags(array($cache_tag));
      return "Success";
    }
    else {
      return "Failure";
    }
  }

}
