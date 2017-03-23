<?php
/**
 * @file
 * DynoBlock Default Layout
 */  
  
class DynoBlockDefaultLayout implements DynoBlockLayout {

  public $layout = array();

  public function init($values) {
    $this->layout['container'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('dyno-default-container'),
      ),
    );
    if (!empty($values['title']) || (!empty($values['title']) && $values['title'] != '<none>')) {
      $this->layout['container']['title'] = DynoWidgetAPI::display($values['title'], array(
        '#tag' => 'h2',
        '#attributes' => array(
          'class' => array('block-title'),
        ),
      ));
    }
    if (!empty($values['body'])) {
      $this->layout['container']['body'] = DynoWidgetAPI::display($values['body']);
    }
    return $this;
  }

  public function preRender($values) {
    return $this->layout;
  }

  public function render() {
    return render($this->layout);
  }
}


class DynoBlockDefaultForm implements DynoBlockForm {

  public $form = array();

  public function init() {
    return $this;
  }

  public function build($form_state = array()) {
    $this->form_state = $form_state;
    
    $values = !empty ($this->form_state['values']) ? $this->form_state['values'] : $this->form_state;
    $this->form['title'] = DynoWidgetAPI::element($this->form_state, 'DTextField', array(
      '#title' => t('Title'),
      '#default_value' => !empty($values['title']['value']) ? $values['title']['value'] : '',
    ));
    $this->form['body'] = DynoWidgetAPI::element($this->form_state, 'DCkeditorField', array(
      '#title' => t('Title'),
      '#default_value' => !empty($values['body']['value']['value']) ? $values['body']['value']['value'] : '',
    ));
    return $this;
  }
  
  public function widgetForm(&$form_state = array(), $items, $delta) {
    
  }

  public function formSubmit(&$form_state) {
    // .. process form values
  }

  public function render() {
    return render($this->form);
  }
}
