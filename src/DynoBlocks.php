<?php

namespace Drupal\dynoblock;


class DynoBlocks {

  public static $blocks = array();
  public static $themes = array();
  public static $widgets = array();

  private $dynodb;

  public function __construct(DynoBlocksDb $dynodb) {
    $this->dynodb = $dynodb;
  }

  public static function getTheme($id) {
    if (empty(self::$themes)) {
      self::$themes = self::getThemes();
    }
    return array_key_exists($id, self::$themes) ? self::$themes[$id] : NULL;
  }

  public static function getThemes() {
    $themes = array();
    foreach (module_implements('dynoblock_themes') as $module) {
      $function = $module . '_dynoblock_themes';
      $theme = $function();
      foreach ($theme as &$thm) {
        $thm['full_path'] = drupal_get_path('module', $module) . '/' . $thm['path'];
        $thm['module'] = $module;
      }
      $themes += $theme;
    }
    return self::$themes = $themes;
  }

  public static function getWidget($id) {
    $widgets = DynoBlocks::loadWidgets();
    return array_key_exists($id, $widgets) ? $widgets[$id] : NULL;
  }

  public static function loadWidgets() {
    $widgets = array();
    if (empty(self::$widgets)) {
      foreach (module_implements('dynoblock_widgets') as $module) {
        $function = $module . '_dynoblock_widgets';
        $widget = $function();
        foreach ($widget as &$w) {
          $w['module'] = $module;
          $w['parent_theme'] = self::getTheme($w['properties']['theme']);
          if (!empty($w['parent_theme']['handler']) && !empty($w['parent_theme']['handler']['class']) && !empty($w['parent_theme']['handler']['file'])) {
            module_load_include('inc', $module, $w['parent_theme']['path'] . '/' . $w['parent_theme']['handler']['file']);
            $w['parent_theme']['handler'] = new $w['parent_theme']['handler']['class']();
          }
        }
        $widgets += $widget;
      }
      self::$widgets = $widgets;
      return $widgets;
    }
    else {
      return self::$widgets;
    }
  }

  public static function getBlocks($rid) {
    return self::$blocks = DynoBlocksDb::getBlocks($rid);
  }

  public static function displayBlocks($blocks, $entity = array()) {
    $render = array();
    foreach ($blocks as $delta => $block) {
      $data = $block['data'];
      $id = !empty($data['widget']) ? $data['widget'] : $data['layout_id'];
      $layout = _dynoblock_find_layout_handler($id);
      $widget = self::getWidget($data['widget']);
      if ($layout && self::isDisplayable($data)) {
        $path = _dynoblock_find_layout_path($id);
        $layout->directory = $path;
        $layout->entity = $entity;
        $html = $layout->init($data)->preRender($data);
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

  public static function renderNewBlock($block, $html) {
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

  public static function wrapEditBlock($html) {
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

  public static function dynoRegion($rid, $nid = NULL, $label = NULL) {
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

  public static function renderDynoBlocks($rid, $entity = array()) {
    $blocks = self::getBlocks($rid);
    $blocks = self::displayBlocks($blocks, $entity);
    return array(
      '#type' => 'markup',
      '#markup' => render($blocks),
    );
  }

  public static function isDisplayable($block) {
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