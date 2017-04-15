<?php
  
/**
 * @file
 * Hooks provided by the Dynoblocks module.
 *
 * Dynoblocks (Dynamic Blocks) is a module that allows you to add Dynamic, Felxible Block
 * Style content inline to any page holding a DynoRegion (Dynamic Region).
 *
 *
 * Modules wanting to eextend this need to follow these strict guidlines:
 *
 *  - If module is implementing its own custom theme(s),
 *    A directory named "themes" needs to be added into its module directory.
 *
 *    - Each theme needs at least two files:
 *      - theme.inc
 *        Contains theme classes for its widgets.
 *        @see abstract class DCBComponentTheme
 *      - fields.inc
 *        Contains field classes for its widgets. 
 *        @see abstract class DCBField
 *
 *
 *
 *  - If a module is implementing custom widgets to a custom theme:
 *    
 *    - Each widget must have its own directory inside of the theme directory in which it is for.
 *    - The directory and widget id/name must be the same.
 *    - The widget directory must contain an file (same name as directory & widget id/name).inc
 *    - All css, javascript, and images partaining to the widget should reside in it's widget directory.
 *    
 *    
 */
  
  
/**
 * Hook to allow custom themes into DCB.
 *
 * - Each themes array key needs to be the same as the theme directory.
 *
 * @return $themes
 *  An array of themes that provide widgets.
 */
function hook_dcb_themes(){
  return array();
}
