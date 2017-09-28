<?php

/**
 * @File: DCB Database service.
 */

namespace Drupal\dcb\Service;

use Drupal\Core\Database\Connection;

/**
 * Class DCBDb.
 *
 * @package Drupal\dynoblock\Service
 */
class DCBDb {

  /**
   * @var string
   */
  static $db_table = 'dcb';

  static $db_revision_table = 'dcb_revision';

  /**
   * @var Connection
   */
  private $database;

  /**
   * DCBDb constructor.
   *
   * @param Connection $database
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * @param $record
   * @return \Drupal\Core\Database\StatementInterface|int|null
   */
  public function save($record) {
    $this->database->merge(self::$db_table)
      ->keys(
        [
          'rid' => $record['rid'],
          'bid' => $record['bid'],
          'revisionid' => $record['revisionid'],
        ]
      )
      ->fields(
        [
          'data' => serialize($record['data']),
          'weight' => $record['weight'],
        ]
      )
      ->execute();

    $this->database->merge(self::$db_revision_table)
      ->keys(
        [
          'rid' => $record['rid'],
          'revisionid' => $record['revisionid'],
        ]
      )
      ->fields(
        [
          'status' => $record['status'],
        ]
      )
      ->execute();

    return TRUE;
  }

  /**
   * @param $rid
   * @return array
   */
  public function getBlocks($rid, $revision) {
    $query = $this->database->select(self::$db_table, 'd');
    $query->fields('d', ['rid', 'bid', 'data', 'weight', 'revisionid']);
    $query->addField('revision', 'status');
    $query->join(self::$db_revision_table, 'revision', 'd.rid = revision.rid AND d.revisionid = revision.revisionid');
    $query->orderBy('d.bid', 'ASC');
    $query->condition('d.rid', $rid, '=');
    $query->condition('d.revisionid', $revision, '=');
    $result = $query->execute();
    $results = [];
    while ($record = $result->fetchAssoc()) {
      $record['data'] = unserialize($record['data']);
      $results[] = $record;
    }
    return $results;
  }

  /**
   * @param $rid
   * @param $bid
   * @return bool|mixed
   */
  public function getBlock($rid, $bid, $revision) {
    $query = $this->database->select(self::$db_table, 'd');
    $query->fields('d', ['rid', 'bid', 'data', 'weight', 'revisionid']);
    $query->addField('revision', 'status');
    $query->join(self::$db_revision_table, 'revision', 'd.rid = revision.rid AND d.revisionid = revision.revisionid');
    $query->orderBy('d.bid', 'ASC');
    $query->condition('d.rid', $rid, '=');
    $query->condition('d.bid', $bid, '=');
    $query->condition('d.revisionid', $revision, '=');
    $result = $query->execute();
    $results = [];
    while ($record = $result->fetchAssoc()) {
      $record['data'] = unserialize($record['data']);
      $results = $record;
    }
    return $results;
  }

  /**
   * @param $rid
   * @param $bid
   * @return int
   */
  public function remove($rid, $bid, $revision) {
    $delete = $this->database->delete(self::$db_table)
      ->condition('rid', $rid)
      ->condition('bid', $bid)
      ->condition('revisionid', $revision)
      ->execute();
    return $delete;
  }

}
