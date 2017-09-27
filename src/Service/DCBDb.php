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
    return $this->database->merge(self::$db_table)
      ->keys(
        [
          'rid' => $record['data']['meta']['rid'],
          'bid' => $record['data']['meta']['bid'],
        ]
      )
      ->fields(
        [
          'data' => serialize($record['data']),
          'conditions' => serialize($record['conditions']),
          'weight' => $record['weight'],
        ]
      )
      ->execute();
  }

  /**
   * @param $rid
   * @return array
   */
  public function getBlocks($rid) {
    $query = $this->database->select(self::$db_table, 'd');
    $query->fields('d', ['rid', 'bid', 'data', 'weight', 'conditions'])
      ->orderBy('bid', 'ASC')
      ->condition('rid', $rid, '=');
    $result = $query->execute();
    $results = [];
    while ($record = $result->fetchAssoc()) {
      $record['data'] = unserialize($record['data']);
      $record['conditions'] = unserialize($record['conditions']);
      $results[] = $record;
    }

    return $results;
  }

  /**
   * @param $rid
   * @param $bid
   * @return bool|mixed
   */
  public function getBlock($rid, $bid) {
    $query = $this->database->select(self::$db_table, 'd');
    $query->fields('d', ['rid', 'bid', 'data','weight', 'conditions'])
      ->orderBy('bid', 'ASC')
      ->condition('rid', $rid, '=')
      ->condition('bid', $bid, '=');
    $result = $query->execute();
    $results = [];
    while ($record = $result->fetchAssoc()) {
      $record['data'] = unserialize($record['data']);
      $record['conditions'] = unserialize($record['conditions']);
      $results[] = $record;
    }

    return $results;
  }

  /**
   * @param $rid
   * @param $bid
   * @return int
   */
  public function remove($rid, $bid) {
    $delete = $this->database->delete(self::$db_table)
      ->condition('rid', $rid)
      ->condition('bid', $bid)
      ->execute();
    return $delete;
  }

}
