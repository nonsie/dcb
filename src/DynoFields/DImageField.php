<?php

namespace Drupal\dynoblock\DynoFields;

use Drupal\dynoblock\DynoField;

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
