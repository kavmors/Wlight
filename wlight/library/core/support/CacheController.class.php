<?php
/**
 * 缓存控制
 * @author  KavMors(kavmors@163.com)
 */

namespace wlight\core\support;
use wlight\util\DbHelper;
use wlight\runtime\Log;

include_once (DIR_ROOT.'/wlight/library/util/DbHelper.class.php');

class CacheController {
  private $db;
  private $tableMsg;
  private $tableRetry;

  public function __construct() {
    $helper = new DbHelper;
    $this->db = $helper->getConnector();

    $this->tableMsg = DB_PREFIX.'_cache_msg';
    $this->tableRetry = DB_PREFIX.'_cache_retry';
  }

  /**
   * 添加消息缓存
   * @param string $createTime
   * @param string $type
   * @param string $key
   * @param string $target
   * @return boolean
   */
  public function putMsg($createTime, $type, $key, $target = '') {
    if (empty($type) || empty($key)) {
      return false;
    }
    try {
      $this->db->beginTransaction();
      $this->db->exec("DELETE FROM `$this->tableMsg` WHERE `type` = '$type' AND `key` = '$key'");
      if ($target != '') {
        $this->db->exec("INSERT INTO `$this->tableMsg` VALUES('$key', '$target', '$type', $createTime)");
      }
      $this->cleanMsg();
      $this->db->commit();
      return true;
    } catch (\PDOException $e) {
      $this->db->rollBack();
      Log::e($e);
      return false;
    }
  }

  /**
   * 添加重试缓存
   * @param string $createTime
   * @param string $key
   * @param string $reply
   * @return boolean
   */
  public function putRetry($createTime, $key, $reply) {
    if (empty($key)) {
      return false;
    }
    try {
      $this->db->beginTransaction();
      if ($reply == '') {
        $ret = $this->db->prepare("INSERT INTO `$this->tableRetry` VALUES(?, ?, ?)");
        $ret->execute(array($key, '', $createTime));
        $this->cleanRetry();
      } else {
        $ret = $this->db->prepare("UPDATE `$this->tableRetry` SET `reply` = ?, `priority` = ? WHERE `key` = ?");
        $ret->execute(array($reply, $createTime, $key));
      }
      $this->db->commit();
      return true;
    } catch (\PDOException $e) {
      $this->db->rollBack();
      Log::e($e);
      return false;
    }
  }

  /**
   * 查询消息缓存
   * @param string $type
   * @param string $key
   * @return string
   */
  public function getMsg($type, $key) {
    if (empty($type) || empty($key)) {
      return '';
    }
    $sql = "SELECT `target`
            FROM `$this->tableMsg`
            WHERE `type` = ? AND `key` = ?
            ";
    $result = $this->db->prepare($sql);
    $result->execute(array($type, $key));
    $result = $result->fetchAll(\PDO::FETCH_ASSOC);
    if (count($result) == 0) {    //没有记录
      return '';
    } else {
      return $result[0]['target'];
    }
  }

  /**
   * 查询重试缓存,字串表示已执行完成的结果,false表示没有键,空串表示执行中
   * @param string $key
   * @return string
   */
  public function getRetry($key) {
    if (empty($key)) {
      return false;
    }
    $sql = "SELECT `reply`
            FROM `$this->tableRetry`
            WHERE `key` = ?
            ";
    $result = $this->db->prepare($sql);
    $result->execute(array($key));
    $result = $result->fetchAll(\PDO::FETCH_ASSOC);
    if (count($result) == 0) {
      return false;
    }
    return $result[0]['reply'];
  }

  /**
   * 返回用于消息缓存的键值
   * @param array $postClass
   * @return string 键值,不缓存时返回空
   */
  public function keyMsg($postClass) {
    switch ($postClass['MsgType']) {
      case 'text': return $postClass['Content'];
      case 'voice': return isset($postClass['Recognition']) ? $postClass['Recognition'] : '';
      case 'event': {
        switch ($postClass['Event']) {
          case 'CLICK': return $postClass['EventKey'];
          default: return '';
        }
      }
      default: return '';
    }
    return '';
  }

  /**
   * 返回用于重试缓存的键值
   * @param array $postClass
   * @return string 键值,不缓存时返回空
   */
  public function keyRetry($postClass) {
    if (isset($postClass['MsgId'])) {
      return $postClass['MsgId'];
    } else {
      return $postClass['FromUserName']. $postClass['CreateTime'];
    }
  }

  /**
   * 根据消息类型获取执行文件夹的路径
   * @param object $postClass
   * @return string 文件夹路径
   */
  public function getPathByType($postClass) {
    if ($postClass['MsgType'] == 'event') {
      return MSG_ROOT.'/event/'.$postClass['Event'];
    } else {
      return MSG_ROOT.'/'.$postClass['MsgType'];
    }
  }

  private function cleanMsg() {
    $max = MAX_CACHE;
    try {
      $result = $this->db->query("SELECT `priority` FROM `$this->tableMsg` ORDER BY `priority` DESC LIMIT $max, 1");
      $result = $result->fetchAll(\PDO::FETCH_ASSOC);
      if (count($result) == 0) {
        return ;
      }
      $maxTime = $result[0]['priority'];

      $this->db->exec("DELETE FROM `$this->tableMsg` WHERE `priority` <= $maxTime");
      return true;
    } catch (\PDOException $e) {
      Log::e($e);
      return false;
    }
  }

  private function cleanRetry() {
    $max = MAX_CACHE;
    try {
      $result = $this->db->query("SELECT `priority` FROM `$this->tableRetry` ORDER BY `priority` DESC LIMIT $max, 1");
      $result = $result->fetchAll(\PDO::FETCH_ASSOC);
      if (count($result) == 0) {
        return ;
      }
      $maxTime = $result[0]['priority'];

      $this->db->exec("DELETE FROM `$this->tableRetry` WHERE `priority` <= $maxTime");
      return true;
    } catch (\PDOException $e) {
      Log::e($e);
      return false;
    }
  }
}

?>