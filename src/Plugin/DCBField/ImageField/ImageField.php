<?php

/**
 * @File: Defines the Image Upload DCB field.
 */

namespace Drupal\dcb\Plugin\DCBField\ImageField;

use Drupal\dcb\Plugin\DCBField\DCBFieldBase;
use Drupal\file\Entity\File;

/**
 * Provides a 'Image Upload' DCBField Widget.
 *
 * @DCBField(
 *   id = "image_field",
 *   name = @Translation("Image Upload"),
 * )
 */
class ImageField extends DCBFieldBase {

  /**
   * @param array $properties
   * @return mixed
   */
  public function form($properties = []) {
    $field['image'] = [
      '#type' => 'managed_file',
      '#title' => isset($properties['#title']) ? $properties['#title'] : t('Image'),
      '#default_value' => !empty($properties['#default_value']['value']['image']) ? $properties['#default_value']['value']['image'] : NULL,
      '#description' => isset($properties['#description']) ? $properties['#description'] : '' . t('Allowed extensions: gif png jpg jpeg'),
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'file_validate_extensions' => ['gif png jpg jpeg'],
      ],
      '#attributes' => [
        'data-widget' => 'Card',
      ],
    ];
    if (!empty($properties['#default_value']['value']['image'])) {
      $field['thumb'] = [
        '#type' => 'container',
        '#theme' => 'dcb_image_thumbnail',
        '#fid' => $properties['#default_value']['value']['image'],
      ];
    }
    $field['alt'] = [
      '#type' => 'textfield',
      '#title' => t('Alt Tag'),
      '#default_value' => !empty($properties['#default_value']['value']['alt']) ? $properties['#default_value']['value']['alt'] : '',
    ];
    $this->setFormElement($field);
    $this->field['value'] += [
      '#type' => 'fieldset',
      '#title' => t('Image'),
    ];
    return $this->field;
  }

  /**
   * @param $value
   * @param $bid
   */
  public function onSubmit($value, $bid) {
    // .. process form values
    if (!empty($value['image'][0])) {
      $file = File::load($value['image'][0]);
      // Change status to permanent.
      $file->setPermanent();
      // Save.
      $file->save();
      // Record that the module is using the file.
      $this->fileUsage->add($file, 'dcb', 'field', $bid);
    }
  }

  /**
   * @param $value
   * @param array $settings
   * @return array
   */
  public function render($value, $settings = []) {
    if (isset($value['image']['fid'])) {
      if ($file = File::load($value['image']['fid'])) {
        return $settings + [
            '#theme' => 'image_style',
            '#style_name' => 'large',
            '#uri' => $file->getFileUri(),
            '#width' => '100%',
            '#height' => 'auto',
            '#alt' => $value['alt'],
            '#attributes' => [
              'class' => ['DImageField'],
            ],
          ];
      }
    }
  }
}
