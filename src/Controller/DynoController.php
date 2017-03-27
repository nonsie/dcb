<?php

namespace Drupal\dynoblock\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\dynoblock\DynoBlockForms;
use Drupal\dynoblock\DynoblockWidgetModal;
use Drupal\Component\Serialization\Json;
use Drupal\dynoblock\Service\DynoblockCore;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DynoController extends ControllerBase {

  /**
   * Dynoblock core.
   *
   * @var \Drupal\dynoblock\Service\DynoblockCore
   */
  public $dynoblockCore;

  /**
   * {@inheritdoc}
   */
  public function __construct(DynoblockCore $dynoblockCore) {
    $this->dynoblockCore = $dynoblockCore;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dynoblock.core')
    );
  }

  /**
   * @param $type
   * @param $rid
   * @param $nid
   * @return JsonResponse
   */
  function generate($type, $rid, $nid) {
    $form = DynoBlockForms::generateForm($type, $rid, $nid);
    return new JsonResponse(Json::encode($form));
  }

  /**
   * @return JsonResponse
   */
  function selectorModal() {
    $modal = new DynoblockWidgetModal();
    $modal->init();
    $modal->build();
    //kint($modal);
    $response = array(Json::encode(array(
      'html' => render($modal->modal),
      'sections' => $modal->build(),
      'widgets' => $modal->widgets,
      'themes' => $modal->themes,
      'default_active' => $modal->default_active,
    )));
    return new JsonResponse($response);
  }

  /**
   * @param $method
   * @return JsonResponse
   */
  function save($method) {
    // Check that this is either an edit or new save.
    // If new save, make sure bid does not already exist.
    $output = array('saved' => FALSE);
    if (!empty($_POST['rid'])
      && !empty($_POST['bid'])
      && ($method == 'edit'
        || ($method == 'new' && !$this->dynoblockCore->db->getBlock($_POST['rid'], $_POST['bid'])))) {
      $form = _dynoblock_find_form_handler(!empty($_POST['widget']) ? $_POST['widget'] : NULL);
      if ($form) {
        $form->id = $_POST['widget'];
        $form->formSubmit($_POST);
        $record = array(
          "rid" => $_POST['rid'],
          "bid" => $_POST['bid'],
          "data" => serialize($_POST),
          'weight' => NULL,
          'conditions' => serialize(array(
            'condition_token' => !empty($_POST['condition_token']) ? !empty($_POST['condition_token']) : NULL,
            'condition_operator' => !empty($_POST['condition_operator']) ? !empty($_POST['condition_operator']) : NULL,
            'condition_value' => !empty($_POST['condition_value']) ? !empty($_POST['condition_value']) : NULL,
          ))
        );
        if ($method == 'edit') {
          $action = $this->dynoblockCore->db->update($record);
        }
        else {
          $action = $this->dynoblockCore->db->save($record);
        }
        if ($action) {
          $layout = _dynoblock_find_layout_handler($_POST['widget']);
          if ($layout) {
            $html = $layout->init($_POST)->preRender($_POST);
            // Call theme preRender so it can modify final output.
            $widget = $this->dynoblockCore->getWidget($_POST['widget']);
            if (!empty($widget['parent_theme']['handler'])) {
              $theme_settings = !empty($_POST['global_theme_settings']) ? $_POST['global_theme_settings'] : array();
              $widget['parent_theme']['handler']->preRender($widget, $_POST, $html, $theme_settings);
            }
            $html = render($html);
            if ($method == 'new') {
              $html = $this->dynoblockCore->renderNewBlock($_POST, $html);
            }
            else {
              $html = $this->dynoblockCore->wrapEditBlock($html);
            }
            $output = array(
              'saved' => TRUE,
              'bid' => $_POST['bid'],
              'rid' => $_POST['rid'],
              'handler' => $_POST['widget'],
              'block' => $html,
            );
          }
        }
      }
    }
    return new JsonResponse(Json::encode($output));
  }

  /**
   * @param $rid
   * @param $bid
   *
   * @return JsonResponse
   */
  function remove($rid, $bid) {
    $db = $this->dynoblockCore->db;
    $removed = $db->remove($rid, $bid);
    return new JsonResponse(Json::encode(array('removed' => $removed)));
  }

  /**
   * @param $rid
   * @param $bid
   * @param $nid
   *
   * @return JsonResponse
   */
  function edit($rid, $bid, $nid) {
    $form = DynoBlockForms::editForm($rid, $bid, $nid);
    return new JsonResponse(Json::encode($form));
  }

  /**
   * @param string $type
   * @param $id
   */
  function ajaxLoad($type = 'blocks', $id) {

  }

  /**
   * @param $rid
   * @param $bid
   */
  function update($rid, $bid) {

  }

  /**
   * @return array
   */
  function testpage() {
    //$content['dynoblocks_test_region'] = DynoBlocks::dynoRegion('dynoblocks-test', NULL, 'Test Region');
    //$content['dynoblocks_test_region']['blocks'] = DynoBlocks::renderDynoBlocks('dynoblocks-test');

    $manager = \Drupal::service('plugin.manager.dynoblock');
    $plugins = $manager->getDefinitions();
    $instance = $manager->createInstance($plugins['page_title']['id']);
    //kint($instance->getId());

    $manager = \Drupal::service('plugin.manager.dynofield');
    $plugins = $manager->getDefinitions();
    $instance = $manager->createInstance($plugins['text_field']['id']);
    //kint($instance->getId());

    $build = array(
      '#type' => 'markup',
      '#markup' => t('Hello World!'),
    );

    return $build;
  }

/*  function create() {

  }*/

}
