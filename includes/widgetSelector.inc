<?php
/**
 * @file
 * DynoBlock Widget Select form
 */ 

class DynoblockWidgetModal {

  public $modal = array();
  public $default_active = 'themes';
  public $sections = array(
    'themes' => array(
      'steps' => array(
        // Theme list step.
        array(
          'wrapper' => 'list-group',
          'default_active' => TRUE,
          'name' => 'Dynoblock Themes',
          'step' => 0,
          'type' => 'themes',
        ),
        // Widget select step.
        array(
           'name' => 'Dynoblock Widgets',
           'step' => 1,
           'dynamic' => TRUE,
           'type' => 'theme_widgets',
        ),
        // Widget preview step.
        array(
           'name' => 'Preview',
           'step' => 2,
           'dynamic' => TRUE,
           'type' => 'widget_preview',
        ),
      ),
    ),
    'categories' => array(),
    'favorites' => array(),
    'recently used' => array(),
  );

  public function init() {
    // MODAL WRAPPER
    $this->modal['modal'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('modal fade'),
        'id' => 'dyno-widget-selector',
      ),
    );
    // DIALOG
    $this->modal['modal']['dialog'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('modal-dialog', 'modal-lg'),
      ),
    );
    // CONTENT
    $this->modal['modal']['dialog']['content'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('modal-content'),
      ),
    );

    // HEADER
    $this->modal['modal']['dialog']['content']['header'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('modal-header'),
      ),
    );
    $this->modal['modal']['dialog']['content']['header']['close'] = array(
      '#type' => 'markup',
      '#markup' => '<button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>',
      
    );
    // set navbar
    $this->modal['modal']['dialog']['content']['header']['nav'] = $this->menu();

    $this->modal['modal']['dialog']['content']['body'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('modal-body'),
      ),
    );

    return $this->modal;
  }

  public function menu() {
    // For bootstrap 3.
/*
    $menu = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('navbar navbar-default'),
      ),
    );
    $menu['inner'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('container-fluid'),
      ),
    );
    $menu['inner']['header'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('navbar-header'),
      ),
    );

    $logo = '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">Dynoblocks</a>';

    $menu['inner']['header']['logo'] = array(
      '#type' => 'markup',
      '#markup' => $logo,
    );

    $menu['inner']['menu'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('collapse navbar-collapse'),
      ),
    );


    $links = array(
      array(
        'data' => '<a class="active" data-dyno-section="themes" href="#">Themes</a>',
        'class' => array('active'),
      ),
      array(
        'data' => '<a href="#" data-dyno-section="favorites">Categories</a>',
      ),
    );
    $menu['inner']['menu']['left'] = array(
      '#theme' => 'item_list',
      '#items' => $links,
      '#attributes' => array(
        'class' => array('nav navbar-nav'),
      ),
    );
    $menu['inner']['menu']['middle'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('navbar-form navbar-left'),
      ),
    );
    $menu['inner']['menu']['middle']['search'] = array(
      '#type' => 'markup',
      '#prefix' => '<div class="form-group">',
      '#markup' => '<input type="text" class="form-control" placeholder="Search">',
      '#suffix' => '</div>',
    );
    $links = array(
      array(
        'data' => '<a href="#" data-dyno-section="custom">Favorites</a>',
      ),
      array(
        'data' => '<a href="#" data-dyno-section="custom">Recently Used</a>',
      ),
    );
    $menu['inner']['menu']['right'] = array(
      '#theme' => 'item_list',
      '#items' => $links,
      '#attributes' => array(
        'class' => array('nav navbar-nav navbar-right'),
      ),
    );
*/


     // For bootstrap 4.
    $menu = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('navbar navbar-light bg-faded'),
        'style' => array('max-width: 850px;'),
      ),
    );
    $menu['logo'] = array(
      '#type' => 'markup',
      '#markup' => '<a class="navbar-brand" href="#">Dynoblocks</a>',
    );

    $links = array(
/*
      array(
        'data' => '<a class="nav-link active" data-dyno-section="themes" href="#">Themes</a>',
        'class' => array('active', 'nav-item'),
      ),
      array(
        'data' => '<a href="#" class="nav-link" data-dyno-section="favorites">Categories</a>',
        'class' => array('nav-item'),
      ),
      array(
        'data' => '<a href="#" data-dyno-section="custom" class="nav-link">Favorites</a>',
        'class' => array('nav-item'),
      ),
      array(
        'data' => '<a href="#" data-dyno-section="custom" class="nav-link">Recently Used</a>',
        'class' => array('nav-item'),
      ),
*/
    );
    $menu['menu'] = array(
      '#theme' => 'item_list',
      '#items' => $links,
      '#attributes' => array(
        'class' => array('nav navbar-nav navbar-right pull'),
      ),
    );
    
    $menu['right'] = array(
      '#theme' => 'item_list',
      '#items' => $links,
      '#attributes' => array(
        'class' => array('nav navbar-nav'),
      ),
    );
    $menu['right'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('form-inline pull-xs-right', 'pull-right'),
      ),
    );
/*
    $menu['right']['search'] = array(
      '#type' => 'markup',
      '#prefix' => '<div class="form-group">',
      '#markup' => '<input type="text" class="form-control" placeholder="Search">',
      '#suffix' => '</div>',
    );
*/

    return $menu;
  }

  private function loadWidgets() {
    $widgets = DynoBlocks::loadWidgets();
    foreach ($widgets as $machine => &$widget) {
      $layout = _dynoblock_find_layout_handler($machine);
      if ($layout) {
        $preview = DynoBlockForms::getPreview($widget);
        if ($preview) $preview = render($preview);
        $widget['preview'] = $this->previewHeader($widget) . $preview . $this->previewActions();
        $widget['properties'] = !empty($widget['properties']) ? $widget['properties'] : array();
        $list_display = $this->listDisplay($widget, 2, 'widget');
        $widget['list_display'] = render($list_display);
        // set widgets to themes
        if (array_key_exists($widget['properties']['theme'], $this->themes)) {
          $this->themes[$widget['properties']['theme']]['widgets'][$machine] = $widget;
        }
      }
    }
    $this->widgets = $widgets;
    //$this->sortWidgets();
    //return $this->sortWidgets();
    $this->widgets;
  }
  
  private function previewHeader($widget) {
    $header = array();
    $header['wrapper'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('widget-preview-header'),
      ),
    );
    $header['wrapper']['title'] = array(
      '#type' => 'html_tag',
      '#tag' => 'em',
      '#value' => $widget['label'],
      '#prefix' => '<label>Previewing:</label> ',
      '#attributes' => array(
        'class' => array('widget-label'),
      ),
    );
    return render($header);
  }
  
  private function previewActions() {
    $actions = array();
    $actions['wrapper'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('widget-actions'),
      ),
    );
    $actions['wrapper']['group'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('widget-actions-wrapper'),
      ),
    );
    $actions['wrapper']['group']['select'] = array(
      '#type' => 'html_tag',
      '#tag' => 'a',
      '#value' => 'SELECT',
      '#attributes' => array(
        'href' => '#',
        'class' => array('btn btn-success'),
        'data-dyno-action' => 'select',
        'data-dyno-select' => 'widget',
      ),
    );
    $actions['wrapper']['group']['cancel'] = array(
      '#type' => 'html_tag',
      '#tag' => 'a',
      '#value' => 'CANCEL',
      '#attributes' => array(
        'href' => '#',
        'class' => array('btn btn-danger'),
        'data-dyno-action' => 'cancel',
      ),
    );
    return render($actions);
  }

/*
  private function sortWidgets() {
    foreach ($this->widgets as $machine => $widget) {
      $this->sections['themes']['steps'][1]['items'][$machine] = $widget;
    }
  }
*/

  private function loadThemes() {
    $themes = DynoBlocks::getThemes();
    foreach ($themes as $key => &$theme) {
      $list_display = $this->listDisplay($theme, 1, 'theme');
      $theme['list_display'] = render($list_display);
    }
    $this->themes = $themes;
    $this->sections['themes']['steps'][0]['items'] = $themes;
  }

  public function build() {
    $this->loadThemes();
    $this->loadWidgets();
    return $this->sections;
  }

  public function listDisplay($widget, $step, $type) {
    $item['title'] = array(
      '#type' => 'html_tag',
      '#tag' => 'h4',
      '#value' => $widget['label'],
      '#attributes' => array(
        'class' => array('list-group-item-heading'),
      ),
    );

    $item['body'] = array(
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => !empty($widget['description_short']) ? $widget['description_short'] : '',
      '#attributes' => array(
        'class' => array('list-group-item-text'),
      ),
    );
    
    $list_item = array(
      '#type' => 'html_tag',
      '#tag' => 'a',
      '#value' => render($item),
      '#attributes' => array(
        'class' => array('list-group-item'),
        'href' => '',
        'data-dyno-action' => 'step',
        'data-dyno-step' => $step,
        'data-dyno-item' => $widget['id'],
        'data-dyno-type' => $type,
      ),
    );

    return $list_item;
  }

}
