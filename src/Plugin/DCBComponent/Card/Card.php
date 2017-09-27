<?php

/**
 * @File: Defines the Card component plugin.
 */

namespace Drupal\dcb\Plugin\DCBComponent\Card;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dcb\Base\Component\DCBComponentBase;
use Drupal\dcb\Base\Component\DCBComponentInterface;

/**
 * Provides a 'Card' DCB Component.
 *
 * @DCBComponent(
 *  id = "Card",
 *  name = @Translation("Card"),
 *  description = "A card is a flexible and extensible content container. It includes options for headers and footers, a wide variety of content, contextual background colors, and powerful display options.",
 *  descriptionShort = "A card is a flexible and extensible content container.",
 *  defaultDisplayOption = "dcb-cards-default",
 *  displayOptions = {
 *    "cards-default" = @Translation("Default"),
 *    "cards-blue" = @Translation("Blue"),
 *  },
 *  fieldSets = {
 *    "outer" = {
 *      "fields" = {
 *        "visible" = {
 *          "outer-textfield" = "text_field",
 *        },
 *        "optional" = {
 *          "optional-textfield" = "text_field",
 *        },
 *      },
 *    },
 *    "repeating" = {
 *      {
 *        "settings" = {
 *          "cardinality" = {
 *            "min" = 1,
 *            "max" = 3,
 *          },
 *        },
 *        "themes" = {
 *          "card-item-default" = @Translation("Default"),
 *          "card-item-center" = @Translation("Centered"),
 *        },
 *        "fields" = {
 *          "visible" = {
 *            "inner-textfield" = "text_field",
 *          },
 *          "optional" = {
 *            "inner-optional-textfield" = "text_field",
 *          },
 *        },
 *      },
 *    },
 *  },
 *  formSettings = {
 *    "variant_support" = 1,
 *  },
 * )
 */
class Card extends DCBComponentBase implements DCBComponentInterface {

  public function register() {
    $fields = [];

    $fields['outer-textfield'] = [
      'label' => 'Outer always visible textfield',
    ];

    $fields['optional-textfield'] = [
      'label' => 'Outer optional textfield',
    ];

    $fields['inner-textfield'] = [
      'label' => 'Inner always visible textfield',
    ];

    $fields['inner-optional-textfield'] = [
      'label' => 'Inner Optional Textfield',
    ];

    return $fields;
  }

  /**
   * Gets called when a widget form is submitted.
   *
   * Any processing you need to do before the values are saved needs to be here.
   * One use case is for images & managed_file that needs to save the images.
   *
   */
  public function formSubmit(FormStateInterface $formState) {
    // This loops through the field groups and permanently saves images that have been uploaded.
    $values = $formState->getValue($this->getId());

    foreach ($values as $delta => $value) {
      if (!empty($value['widget']['items']['img']['value'])) {
        $handler = $this->getField('image_field', TRUE);
        $handler->onSubmit($value['widget']['items']['img']['value'], $formState->getValue('bid'));
      }
    }
  }

}
