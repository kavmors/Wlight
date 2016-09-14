<?php
/**
 * 获取统计量及留言
 * @author  KavMors(kavmors@163.com)
 *
 * string getTag(string)
 * array getMessage(array)
 */

namespace wlight\common;
use wlight\util\DbHelper;

include_once (DIR_ROOT.'/wlight/library/util/DbHelper.class.php');

class Statis {
  private $db;
  private $tag;
  private $message;

  /**
   * @throws PDOException
   */
  public function __construct() {
    try {
      $helper = new \wlight\util\DbHelper();
      $this->db = $helper->getConnector();
      $this->tag = DB_PREFIX.'_statis_tag';
      $this->message = DB_PREFIX.'_statis_message';
    } catch (\PDOException $e) {
      $this->sql = null;
      throw $e;
    }
  }

  /**
   * 获取使用量
   * @param string date 可选,查询日期,格式如YYYY-mm-dd
   */
  public function getTag($date = null) {
    $dbname = DB_NAME;
    $result = $this->db->query("SELECT `COLUMN_NAME` AS `key`, `COLUMN_COMMENT` AS `map` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = '$dbname' AND `TABLE_NAME` = '$this->tag'");
    $result = $result->fetchAll(\PDO::FETCH_ASSOC);
    $mapper = array();
    if (is_array($result)) {
      array_shift($result);    //删除'日期'一列
      foreach ($result as $row) {
        $mapper[$row['key']] = array('tag'=>$row['map'], 'data'=>array());
      }
    }

    if ($date == null) {
      $result = $this->db->query("SELECT * FROM `$this->tag` WHERE 1");
    } else {
      $result = $this->db->query("SELECT * FROM `$this->tag` WHERE `date` = '$date'");
    }
    $result = $result->fetchAll(\PDO::FETCH_ASSOC);
    if (count($result) == 0) {
      return null;
    }

    foreach ($result as $key => $field) {
      foreach ($field as $tag => $count) {
        if ($tag == 'date') {
          continue;
        }
        $mapper[$tag]['data'][] = array('date'=>$field['date'], 'count'=>$count);
      }
    }

    return $mapper;
  }

  /**
   * 查询留言
   * @param array $options 可选,查询选项,可选值包括
   *     date(YYYY-mm-dd), type, wechat_id
   */
  public function getMessage($options = null) {
    $opt = array();
    if ($options == null) {
      $options = array();
    }
    if (isset($options['date'])) {
      $opt[] = "`create_time` LIKE '$options[date]%'";
    }
    if (isset($options['type'])) {
      $opt[] = "`msgType` = '$options[type]'";
    }
    if (isset($options['wechat_id'])) {
      $opt[] = "`wechat_id` = '$options[wechat_id]'";
    }
    $o = implode(' AND ', $opt);
    if ($o == '') {
      $o = '1';
    }
    $sql = "SELECT * FROM `$this->message` WHERE $o";
    return $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
  }
}
?>