<?php

/**
 * @file
 * Hooks provided by the DCB (Dynamic Content Builder) module.
 *
 * DCB is a module that allows you to add Dynamic, Flexible Block
 * Style content inline to any page holding a DCB Region.
 *
 * Most of the d7 hooks have been converted to plugins. The only Remaining
 * hook is hook_dcb_themes() which allows you to declare groups of components
 * for organizational purposes. See dcb.module for the implementation of this
 * hook.
 *
 */


/**
 * Hook to allow custom themes into DCB.
 *
 * - Each themes array key needs to be the same as the theme directory.
 * @return array $themes
 *  An array of themes that provide widgets.
 */
function hook_dcb_themes() {
  return [];
}
