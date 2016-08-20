<?php
/**
 * 开发功能统计基础类
 * 用于统计同一功能每天的使用量
 * @author  KavMors(kavmors@163.com)
  */

namespace wlight\core\support;
use wlight\runtime\Log;

include_once (DIR_ROOT.'/wlight/library/util/DbHelper.class.php');

class Statis {
  private $sql;
  private $tag;
  private $message;

  public function __construct() {
    try {
      $helper = new \wlight\util\DbHelper();
      $this->sql = $helper->getConnector();
      $this->tag = DB_PREFIX.'_statis_tag';
      $this->message = DB_PREFIX.'_statis_message';
    } catch (\PDOException $e) {
      Log::e($e);
      $this->sql = null;
    }
  }

  /**
   * 检测连接数据库是否正常
   */
  public function isReady() {
    return $this->sql != null;
  }

  /**
   * 功能统计总次数增加1
   * @param string $key 功能对应的tag字符值
   */
  public function increase($key, $map) {
    $now = date('Y-m-d', time());

    try {
      $this->sql->beginTransaction();
      //检查表结构
      $sqlStm = "DESC `$this->tag` `$key`";

      //若无$key列则修改表结构
      $result = $this->sql->query($sqlStm);
      if ($result==null || count($result->fetchAll(\PDO::FETCH_ASSOC))==0) {
        $this->appendCol($key);
      }

      //更新数据
      $sqlStm = "UPDATE `$this->tag` SET `$key` = `$key`+1 WHERE `date` = '$now'";
      //更新数据失败,则添加新的行
      if ($this->sql->exec($sqlStm)==0) {
        $this->appendRow($key, $now);
      }

      //更新功能统计对应的描述
      $this->updateMap($key, $map);

      $this->sql->commit();
    } catch (\PDOException $e) {
      $this->sql->rollBack();
      Log::e($e);
    }
  }

  /**
   * 增加一条留言
   * @param array $postClass
   */
  public function insertMessage($postClass) {
    $coreKey = $this->getMsgCoreKey($postClass);
    if ($coreKey == '') {
      return;
    }

    $wechatId = $postClass['FromUserName'];
    $createTime = $postClass['CreateTime'];
    $msgType = $postClass['MsgType'];
    if (isset($postClass['EventKey'])) {
      $msgType .= (':'.$postClass['Event']);
    }
    $content = $postClass[$coreKey];

    //清理键值
    unset($postClass['URL']);
    unset($postClass['ToUserName']);
    unset($postClass['FromUserName']);
    unset($postClass['CreateTime']);
    unset($postClass['MsgType']);
    if (isset($postClass['EventKey'])) {
      unset($postClass['Event']);
    }
    unset($postClass[$coreKey]);

    //整理额外信息
    $extra = $this->getMsgExtra($postClass);

    //添加留言
    $sqlStm = "INSERT INTO `$this->message` VALUES(?, ?, ?, ?, ?)";
    $this->sql->prepare($sqlStm)->execute(array($wechatId, date('Y-m-d H:i:s', $createTime), $msgType, $content, $extra));
  }

  /**********************/

  //更改表结构, 插入新列
  private function appendCol($key) {
    $sqlStm = "ALTER TABLE `$this->tag` ADD `$key` int(11) DEFAULT '0'";
    $this->sql->exec($sqlStm);
  }

  private function updateMap($key, $map) {
    $tableMap = $this->tag.'_map';
    $this->sql->exec("DELETE FROM `$tableMap` WHERE `key` = '$key'");
    $this->sql->exec("INSERT INTO `$tableMap` VALUES('$key', '$map')");
  }

  //添加新行()
  private function appendRow($key, $now) {
    //插入新行
    $sqlStm = "INSERT INTO `$this->tag` (`date`, `$key`) VALUES ('$now', 1)";
    $this->sql->exec($sqlStm);

    //插入新行表示每天第一个数据, 此时做清理工作
    $this->deleteExpired();    //过期数据清理
    $this->removeDiscardCol();    //长期不使用列清理
  }

  //计算过期日期, 删除过期元组
  private function deleteExpired() {
    $expire = '-'.STATIS_LIVE.' day';
    $expireDate = date('Y-m-d', strtotime($expire, time()));
    $expireTime = date('Y-m-d 23:59:59', strtotime($expire, time()));

    //删除过期数据
    $this->sql->exec("DELETE FROM `$this->tag` WHERE `date` <= '$expireDate'");
    $this->sql->exec("DELETE FROM `$this->message` WHERE `create_time` <= '$expireTime'");
  }

  //更改表结构, 清理不常用功能的统计列
  private function removeDiscardCol() {
    $lists = $this->getAllList();

    if (!is_array($lists)) {      //非数组抛异常
      return;
    } elseif (count($lists)==0) {    //列数为0直接返回
      return;
    }

    //修改字段表达方式
    foreach ($lists as $key => $value) {
      $lists[$key] = "MAX(`$value[key]`) AS `$value[key]`";
    }
    $lists = implode(', ', $lists);

    //查询各字段的最大值
    $sqlStm = "SELECT $lists FROM `$this->tag`";
    $result = $this->sql->query($sqlStm);
    if ($result!=null) {
      $data = $result->fetchAll(\PDO::FETCH_ASSOC);
      foreach ($data[0] as $key => $value) {
        if (intval($value)==0) {    //{STATIS_LIVE}天内使用次数最大为0
          $this->removeCol($key);   //删除该列
        }
      }
    }
  }

  //更改表结构, 删除一列
  private function removeCol($key) {
    $tableMap = $this->tag.'_map';
    $this->sql->exec("ALTER TABLE `$this->tag` DROP COLUMN `$key`");
    $this->sql->exec("DELETE FROM `$tableMap` WHERE `key` = '$key'");
  }

  //获取所有列字段名
  private function getAllList() {
    $tableMap = $this->tag.'_map';
    $sqlStm = "SELECT `key` FROM `$tableMap`";
    $result = $this->sql->query($sqlStm);
    if ($result!=null) {
      return $result->fetchAll(\PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  }

  //根据msgType返回消息体的核心字段,空串表示不记录留言
  private function getMsgCoreKey($postClass) {
    switch ($postClass['MsgType']) {
      case 'text': return 'Content';
      case 'image': return 'MediaId';
      case 'event': return isset($postClass['EventKey']) ? 'EventKey' : '';
      default: return '';
    }
  }

  //把postClass信息组成xml结构
  private function getMsgExtra($postClass) {
    if (!is_array($postClass) || count($postClass) == 0) {
      return '';
    }
    $xml = "<xml>\n";
    foreach ($postClass as $key => $value) {
       $xml .= "<$key>$value</$key>\n";
    }
    $xml .= "</xml>";
    return $xml;
  }
}
?>