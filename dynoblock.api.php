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



/**
 * Hook to allow custom widgets into Dynoblock.
 *
 * - Each widgets array key needs to be the same as the widgets directory.
 *
 * - Each widget may use the following properties:
 * - Properties begginging with the * character are required
 *  - * id:
 *    The widgets id. This must be the same as its corresponding directory name.
 *
 *  - * label:
 *    The label that will show up in the UI.
 *
 *  - description_short:
 *    The short desciption that is show in the UI for each widget.
 *
 *  - form_settings:
 *    - cardinality:
 *      Setting to -1 will allow for multiple instances of grouped fields.
 *      @see interface DynoBlockForm->widgetForm()
 *
 *  - * layout:
 *    - * file: 
 *      The files name containing a class implementing interface DynoBlockLayout.
 *    - * handler:
 *      The class name implementing interface DynoBlockLayout.
 *
 *  - * form:
 *    - * file: 
 *      The files name containing a class implementing interface DynoBlockForm.
 *    - * handler:
 *      The class name implementing interface DynoBlockForm.
 *
 *  - * properties:
 *    - * theme:
 *      The parent theme the widget belongs to. Must match the themes id/array key property.
 *    - category:
 *      Not used for anything yet. But will be used to categoryize widgets together.
 *    - * dir:
 *      The widgets directory name. The widget must be placed inside its parents themes directory. Its name must match its own id property.
 *    - preview_image:
 *      The widgets preview image filename. Should be placed inside the wigets directory (does not have to be in a folder).
 *
 *
 *
 * @return $widgets
 *  An array of widgets.
 */
function hook_dynoblock_widgets(){
  return array(
    'icon_card' => array(
      'id' => 'icon_card',
      'label' => 'Icon Card',
      'description' => '',
      'description_short' => 'A card is a flexible and extensible content container. It includes options for headers and footers, a wide variety of content, contextual background colors, and powerful display options.',
      'form_settings' => array(
        'cardinality' => -1, // sets cardinality to unlimited
      ),
      'layout' => array(
        'file' => 'icon_card', // the widgets filename without extension. extension must be .inc
        'handler' => 'AAAIconCard__Layout',
      ),
      'form' => array(
        'file' => 'icon_card', // the widgets filename without extension. extension must be .inc
        'handler' => 'AAAIconCard__Form',
      ),
      'properties' => array(
        'theme' => 'aaa',
        'category' => 'aaa',
        'dir' => 'icon_card',
        'preview_image' => 'icon_cards.png',
      ),
    ),
  );
}