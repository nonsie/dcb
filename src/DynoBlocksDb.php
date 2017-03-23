<?php

namespace Drupal\dynoblock;

class DynoBlocksDb {

  static $db_table = 'dynoblock';

  public static function save($record) {
    return drupal_write_record(self::$db_table, $record);
  }

  public static function update($record) {
    return db_update(self::$db_table)
      ->condition('rid', $record['rid'])
      ->condition('bid', $record['bid'])
      ->fields(array(
        'data' => $record['data'],
      ))
      ->execute();
  }

  public static function getBlocks($rid) {
    $query = db_select(self::$db_table, 'd');
    $query->fields('d', array('rid', 'bid', 'data'))
      ->orderBy('bid', 'ASC')
      ->condition('rid', $rid, '=');
    $result = $query->execute();
    $results = array();
    while ($record = $result->fetchAssoc()) {
      $record['data'] = unserialize($record['data']);
      $results[] = $record;
    }
    return $results;
  }

  public static function getBlock($rid, $bid) {
    $query = db_select(self::$db_table, 'd');
    $query->fields('d', array('rid', 'bid', 'data'))
      ->orderBy('bid', 'ASC')
      ->condition('rid', $rid, '=')
      ->condition('bid', $bid, '=');
    $result = $query->execute()->fetchAssoc();
    if ($result) {
      return unserialize($result['data']);
    }
    else {
      return FALSE;
    }
  }

  public static function remove($rid, $bid) {
    $delete = db_delete(self::$db_table)
      ->condition('rid', $rid)
      ->condition('bid', $bid)
      ->execute();
    return $delete;
  }

}