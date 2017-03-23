<?php

namespace Drupal\dynoblock\Plugin\Dynoblock;

use Drupal\dynoblock\DynoblockBase;

/**
 * Provides a 'page Title' Dynoblock Widget.
 *
 * @Dynoblock(
 *   id = "page_title",
 *   name = @Translation("Page Title"),
 *   default_theme = "default",
 *   form_settings = {
 *     "cardinality" = -1,
 *     "variant_support" = 1,
 *   },
 *   properties = {
 *    "theme" = "aaa",
 *    "dir" = "banner",
 *    "preview_image" = "banner.png",
 *   }
 * )
 */
class PageTitle extends DynoblockBase {

  public function init() {
    // TODO: ....
  }

}
