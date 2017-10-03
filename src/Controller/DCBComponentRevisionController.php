<?php

namespace Drupal\dcb\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\dcb\Entity\DCBComponentInterface;

/**
 * Class DCBComponentController.
 *
 *  Returns responses for DCB Component routes.
 */
class DCBComponentRevisionController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a DCB Component  revision.
   *
   * @param int $dcb_component_revision
   *   The DCB Component  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($dcb_component_revision) {
    $dcb_component = $this->entityManager()->getStorage('dcb_component')->loadRevision($dcb_component_revision);
    $view_builder = $this->entityManager()->getViewBuilder('dcb_component');

    return $view_builder->view($dcb_component);
  }

  /**
   * Page title callback for a DCB Component  revision.
   *
   * @param int $dcb_component_revision
   *   The DCB Component  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($dcb_component_revision) {
    $dcb_component = $this->entityManager()->getStorage('dcb_component')->loadRevision($dcb_component_revision);
    return $this->t('Revision of %title from %date', ['%title' => $dcb_component->label(), '%date' => format_date($dcb_component->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a DCB Component .
   *
   * @param \Drupal\dcb\Entity\DCBComponentInterface $dcb_component
   *   A DCB Component  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(DCBComponentInterface $dcb_component) {
    $account = $this->currentUser();
    $langcode = $dcb_component->language()->getId();
    $langname = $dcb_component->language()->getName();
    $languages = $dcb_component->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $dcb_component_storage = $this->entityManager()->getStorage('dcb_component');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $dcb_component->label()]) : $this->t('Revisions for %title', ['%title' => $dcb_component->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all dcb component revisions") || $account->hasPermission('administer dcb component entities')));
    $delete_permission = (($account->hasPermission("delete all dcb component revisions") || $account->hasPermission('administer dcb component entities')));

    $rows = [];

    $vids = $dcb_component_storage->revisionIds($dcb_component);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\dcb\DCBComponentInterface $revision */
      $revision = $dcb_component_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $dcb_component->getRevisionId()) {
          $link = $this->l($date, new Url('entity.dcb_component.revision', ['dcb_component' => $dcb_component->id(), 'dcb_component_revision' => $vid]));
        }
        else {
          $link = $dcb_component->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.dcb_component.translation_revert', ['dcb_component' => $dcb_component->id(), 'dcb_component_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.dcb_component.revision_revert', ['dcb_component' => $dcb_component->id(), 'dcb_component_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.dcb_component.revision_delete', ['dcb_component' => $dcb_component->id(), 'dcb_component_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['dcb_component_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
