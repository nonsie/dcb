<?php
/**
 * @file
 * Default form for the Multi Widget
 */
  
class DMultiWidget__Display implements DynoBlockLayout {

  public $layout = array();

  public function init($values) {
    $this->layout['container'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('dyno-default-container'),
      ),
    );
    if (!empty($values['title']) || (!empty($values['title']) && $values['title'] != '<none>')) {
      $this->layout['container']['title'] = array(
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => token_replace($values['title']),
        '#attributes' => array(
          'class' => array('dynoblock-title'),
        ),
      );
    }
    if (!empty($values['body'])) {
      $this->layout['container']['body'] = array(
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => token_replace($values['body']),
        '#attributes' => array(
          'class' => array('dynoblock-body'),
        ),
      );
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


class DMultiWidget__Form implements DynoBlockForm {

  public $form = array();
  public $form_state = array();
  public $default_theme;
  public $themes = array();
  
  /**
   * Gets called right after it is __constructed.
   * 
   * Should load any dependancies, set themes, and set any other properties it may need. 
   */
  public function init() {
//     aaa_cards_load_dependancies();
    return $this;
  }
  
  /**
   * Main function for building the widgets form form.
   * Fields that are global for this widget need to go here.
   *
   * Some widgets dont have any global fields and rely on the selected theme to add some.
   * @see abstract class DynoWidgetTheme
   * 
   */
  public function build($form_state = array()) {
    // sets the $form_state that may or may not be used in other places.
    $this->form_state = $form_state;
    return $this;
  }
  
  /**
   * Gets called wwhen a widget has multiple cardinality.
   * @see field_settings in hook_dynoblock_widgets();
   *
   * This allows you to "add another" to a group of fields.
   * 
   */
  public function widgetForm(&$form_state = array(), $items, $delta) {
    $collapsed = TRUE;
    if (isset($form_state['triggering_element']['#attributes']['delta'])) {
      $trigger_delta = $form_state['triggering_element']['#attributes']['delta'];
      if ($trigger_delta == $delta) $collapsed = FALSE;
    } 
    $element['items'] = array(
      '#type' => 'fieldset',
      '#title' => t('Item @delta', array(
        '@delta' => ($delta + 1),
      )),
      '#collapsed' => $collapsed,
      '#collapsible' => TRUE,
      '#attributes' => array(
        'id' => 'widget-group-' . $delta,
      ),
    ) + $this->addFields(!empty($items[$delta]) ? $items[$delta] : array() , $delta);
    return $element;
  }
  
  /**
   * Helper function to add a group of fields this widget uses..
   *
   *  - @see DynoWidgetAPI::element
   *    Using this allows for many great things:
   *      Allows for you to use pre defined fileds and thier displays that can be interganged between themes and widgets.
   *      These fields classes need to be added to your parent themes field.inc file.
   *      @see abstract class DynoField
   *
   *  - @see DynoBlockForms::themeOptions
   *    This allows you to add custom themes to your group of fields.
   *    These theme classes need to be added to your parent themes theme.inc file.
   *    @see abstract class DynoWidgetTheme
   * 
   * 
   */
  private function addFields($values = array(), $delta) {
    $values = !empty($values['widget']['items']) ? $values['widget']['items'] : $values['input'];
    $item = array();
    $item['widget'] = DynoWidgetAPI::element($this->form_state, 'DSelectWidget', array(
      '#default_value' => !empty($values['widget']['value']) ? $values['widget']['value'] : '',
    ));
    return $item;
  }
  
  /**
   * Gets called when a field element is using method dynoblock_form_ajax_callback.
   *
   * @see DynoBlockForms::fieldAjaxCallback
   *
   * Normally used like any other drupal ajax calback.
   *
   * @return $element or $commands.
   *   An array key by either:
   *    - return_element:
   *      A form element inside $form
   *    - commands:
   *      an array of ajax commands.
   *      @see https://api.drupal.org/api/drupal/includes!ajax.inc/group/ajax_commands/7    
   * 
   */
  public function ajaxCallback($form, &$form_state) {
    // this returnes a group of fields after an extra field is selected in the UI.
    //return array('return_element' => $form['widget']);
  }

  /**
   * Gets called a form element is using an ajax #submit method of dynoblock_form_ajax_submit.
   *
   * @see DynoBlockForms::fieldAjaxSubmit
   * You may run the same logic as you would for any other drupal form submit method. 
   * 
   */
  public function ajaxSubmit($form, &$form_state) {
    
  }

  /**
   * Gets called when a widget form is submitted.
   *
   * Any processing you need to do before the values are saved needs to be here.
   * One use case is for images & managed_file that needs to save the images.
   * 
   */
  public function formSubmit(&$form_state) {
    // This loops through the field groups and permanatly saves images that have been uploaded.
    $values = $form_state[$this->id];
    $items = array();
    foreach ($values as $delta => $value) {
      if (!empty($value['widget']['items']['img']['value'])) {
        $handler = new DImageField($form_state);
        $handler->onSubmit($value['widget']['items']['img']['value']);
      }
    }
  }

  /**
   * Used to render the final form. 
   */
  public function render() {
    return render($this->form);
  }
  
}