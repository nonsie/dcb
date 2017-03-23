<?php
/**
 * @file
 * DynoBlock Default Fields
 */ 

/**
 * Image field class. used for files and images
 */
class DImageField extends DynoField {

  public function form($properties = array()) {
    $field['image'] = array(
      '#type' => 'managed_file',
      '#title' => t('Image'),
      '#default_value' => !empty($properties['#default_value']['image']['fid']) ? $properties['#default_value']['image']['fid'] : '',
      '#description' => t( 'Allowed extensions: gif png jpg jpeg' ),
      '#upload_location' => 'public://',
      '#upload_validators' => array(
        'file_validate_extensions' => array( 'gif png jpg jpeg' ),
      ),
      '#theme' => 'dynoblock_image_thumbnail',
    );
    $field['alt'] = array(
      '#type' => 'textfield',
      '#title' => t('Alt Tag'),
      '#default_value' => !empty($properties['#default_value']['alt']) ? $properties['#default_value']['alt'] : '',
    );
    $this->setFormElement($field);
    $this->field['value'] += array(
      '#type' => 'fieldset',
      '#title' => t('Image'),
      '#collapsed' => FALSE,
      '#collapsible' => TRUE,
    );
    return $this->field;
  }

  public function onSubmit($value) {
    // .. process form values
    if (!empty($value['image']['fid'])) {
      $file = file_load($value['image']['fid']);
      // Change status to permanent.
      $file->status = FILE_STATUS_PERMANENT;
      // Save.
      $saved = file_save($file);
      // Record that the module (in this example, user module) is using the file.
      file_usage_add($file, 'dynoblock', 'field', $this->form_state['bid']);
    }
  }

  public static function render($value, $settings = array()) {
    if (isset($value['image']['fid'])) {
      if ($file = file_load($value['image']['fid'])) {
        $image = image_load($file->uri);
        if (!empty($image->source)) {
          return $settings + array(
            '#theme' => 'image_style',
            '#style_name' => 'large',
            '#path' => $image->source,
            '#width' => '100%',
            '#height' => 'auto',
            '#alt' => $value['alt'],
            '#attributes' => array(
              'class' => array('DImageField'),
            ),
          );
        }
      }
    }
  }
}

/**
 * Default Textfield.
 */
class DTextField extends DynoField {

  public function form($properties = array()) {
    $field = $properties + array(
      '#type' => 'textfield',
      '#maxlength' => 256,
    );
    $this->setFormElement($field);
    return $this->field;
  }

  public static function render($value, $settings = array()) {
    if (!empty($value)) {
      return $settings + array(
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $value,
        '#attributes' => array(
          'class' => array('dyno-TextField'),
        ),
      );
    }
  }
}

/**
 * Default Icon Textfield.
 */
class DIconField extends DTextField {

  public function form($properties = array()) {
    $field = $properties + array(
      '#type' => 'textfield',
      '#title' => t('Icon Class'),
    );
    $this->setFormElement($field);
    return $this->field;
  }

  public static function render($value, $settings = array()) {
    if (!empty($value)) {
      return $settings + array(
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => '',
        '#attributes' => array(
          'class' => array($value, 'DIconField'),
        ),
      );
    }
  }
}

/**
 * Default Select Field.
 */
class DSelectField extends DynoField {

  public function form($properties = array()) {
    $field = $properties + array(
      '#type' => 'select',
    );
    $this->setFormElement($field);
    return $this->field;
  }

  public static function render($value, $settings = array()) {
    if (!empty($value)) {
      return array(
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $value,
        '#attributes' => array(
          'class' => array('dyno-TextField'),
        ),
      );
    }
  }
}


class DTextArea extends DynoField {

  public function form($properties = array()) {
    $field = $properties + array(
      '#type' => 'textarea',
    );
    $this->setFormElement($field);
    return $this->field;
  }

  public static function render($value, $settings = array()) {
    if (!empty($value['value']) || (!empty($value) && is_string($value))) {
      $value_text = isset($value['value']) ? $value['value'] : $value;
      self::filter($value_text);
      return $settings + array(
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => token_replace($value_text),
        '#desctiption' => t('Use :script::/script: instead of <script></script> if you would like to add inline javascript.'),
        '#attributes' => array(
          'class' => array('dyno-DTextArea'),
        ),
      );
    }
  }
  
  public static function filter(&$text) {
    $filtered = preg_replace(array("^\[script(.*?)\]^", "^\[\/script(.*?)\]^"), array('<script${1}>', '</script>'), $text);
    if ($filtered) $text = $filtered;
  }
}

/**
 * Default Widget Selection Field.
 */
class DSelectWidget extends DynoField {

  public function form($properties = array()) {
    $container_id = DynoBlockForms::randId();
    $field['widget_form'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'id' => $container_id,
      ),
    );
    $field['widget'] = array(
      '#type' => 'select',
      '#title' => t('Widget'),
      '#weight' => -100,
      '#default_value' => !empty($properties['#default_value']['widget']) ? $properties['#default_value']['widget'] : NULL,
      '#ajax' => array(
        'wrapper' => $container_id,
        'callback' => DynoBlockForms::fieldWidgetAjaxCallback,
        'handler' => get_called_class(),
      ),
      '#options' => self::loadWidgets(),
    );
    if (!empty($properties['#default_value']['widget'])) {
      $field['widget_form']['widget'] = $this->buildWIdgetForm($properties['#default_value']['widget'], $properties['#default_value']['widget_form']);
    }
    $this->setFormElement($field);
    return $this->field;
  }
  
  private static function loadWidgets() {
    $widgets = array();
    if (!empty(DynoBlocks::$widgets)) {
      foreach (DynoBlocks::$widgets as $key => $widget) {
        $widgets[$key] = $widget['label'];
      }
    }
    array_unshift($widgets, t('- Select Widget -'));
    return $widgets;
  }
  
  public static function getWidget($name) {
    if (!empty(DynoBlocks::$widgets)) {
      if (array_key_exists($name, DynoBlocks::$widgets)) return DynoBlocks::$widgets[$name];
    }
  }
  
  public function buildWIdgetForm($widget, $default_values) {
    $form = array();
    if ($widget = self::getWidget($widget)) {
      if ($widget_form = _dynoblock_find_form_handler($widget['id'])) {
        $widget_form->init()->build($default_values['widget']);
        DynoBlockForms::buildThemeSelection($widget, $widget_form, $default_values['widget']);
        $cardinality = isset($widget['form_settings']['cardinality']) ? $widget['form_settings']['cardinality'] : NULL;
        if ($cardinality === NULL) {
          return $widget_form->form;
        }
        else {
          DynoBlockForms::buildWidgetForm($widget, $widget_form, $default_values['widget']);
        }
        $form = $widget_form->form;
      }
    }
    return $form;
  }
  
  public function onAjax($form, &$form_state) {
    $item = $form;
    if (!empty($form_state['triggering_element']['#parents'])) {
      foreach ($form_state['triggering_element']['#parents'] as $parent) {
        $item = $item[$parent];
        if ($parent === 'value') {
          break;
        }
      }
    }
    return array('return_element' => $item['widget_form']);
  }

  public static function render($value, $settings = array()) {
    return NULL; // #TODO: Logic that gets all selected widgets and renders them.
  }
}


/**
 * Default wysiwyg ckeditor field class.
 */
class DCkeditorField extends DTextArea {
  public function form($properties = array()) {

    $field = $properties + array(
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#wysiwyg' => TRUE,
    );
    $this->setFormElement($field);
    return $this->field;
  }
  
  public static function render($value, $settings = array()) {
    if (empty($value)) {
      return NULL;
    }
    $display = parent::render($value, $settings);
    $display['#type'] = 'markup';
    $display['#markup'] = $value['value'];
    $container = array(
      '#type' => 'container',
      'wysiwyg_content' => $display,
      '#attributes' => array(
        'class' => array('custom-text'),
      ),
    );
    return $container;
  }
}