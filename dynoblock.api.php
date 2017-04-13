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
 *        @see abstract class DynoWidgetTheme
 *      - fields.inc
 *        Contains field classes for its widgets. 
 *        @see abstract class DynoField
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
 * Hook to allow custom themes into Dynoblock.
 *
 * - Each themes array key needs to be the same as the theme directory.
 *
 * @return $themes
 *  An array of themes that provide widgets.
 */
function hook_dynoblock_themes(){
  return array(
    'bootstrap4' => array(
      'id' => 'bootstrap4',
      'label' => 'Bootstrap 4',
      'description_short' => 'The Bootstrap 4 Theme!', 
      'module' => 'aaa_dynoblock',
      'path' => 'themes/bootstrap4',
    ),
    'aaa' => array(
      'id' => 'aaa',
      'label' => 'AAA',
      'module' => 'aaa_dynoblock',
      'description_short' => 'AAA Custom Theme',   
      'path' => 'themes/aaa', 
      'files' => array(
        'theme.inc',
        'fields.inc',
      ),
    ),
  );
}
