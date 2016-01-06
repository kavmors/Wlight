<?php
/**
 * 开发功能统计基础类
 * 用于统计同一功能每天的使用量
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\sta;
use wlight\runtime\Log;

include_once (DIR_ROOT.'/wlight/library/util/DbHelper.class.php');

class Tag {
  private $sql;
  private $table;

  private $helper;

  public function __construct() {
    $this->helper = new \wlight\util\DbHelper();
    $this->sql = $this->helper->getConnector();
    $this->table = DB_PREFIX.'_tag';
  }

  /**
   * 功能统计总次数增加1
   * @param string $key - 功能对应的tag字符值
   */
  public function increase($key, $map) {
    //数据库连接失败直接退出
    if ($this->sql==null) {
      Log::getInstance()->e('DbError', $this->helper->getError());
      return;
    }

    $now = date('Y-m-d', time());

    try {
      //检查表结构
      $sqlStm = "DESC `$this->table` `$key`";
      //若无$key列则修改表结构
      $result = $this->sql->query($sqlStm);
      if ($result==null || count($result->fetchAll())==0) {
        $this->appendCol($key, $map);
      }
      //更新数据
      $sqlStm = "UPDATE `$this->table` SET `$key` = `$key`+1 WHERE `date` = '$now'";
      //更新数据失败,则添加新的行
      if ($this->sql->exec($sqlStm)==0) {
        $this->appendRow($key, $now);
      }
    } catch (\PDOException $e) {
      Log::getInstance()->e('DbError', $e->getMessage());
    }
  }

  //添加新行
  private function appendRow($key, $now) {
    try {
      //插入新行
      $sqlStm = "INSERT INTO `$this->table` (`date`, `$key`) VALUES ('$now', 1)";
      $this->sql->exec($sqlStm);

      //插入新行表示每天第一个数据, 此时做清理工作
      $this->deleteExpiredRow();    //过期数据清理
      $this->removeDiscardCol();    //长期不使用列清理
    } catch (\PDOException $e) {
      Log::getInstance()->e('DbError', $e->getMessage());
    }
  }

  //更改表结构, 插入新列
  private function appendCol($key, $map) {
    try {
      $sqlStm = "ALTER TABLE `$this->table` ADD `$key` int(11) DEFAULT '0'";
      $this->sql->exec($sqlStm);

      $tableMap = $this->table.'_map';
      $sqlStm = "INSERT INTO `$tableMap` VALUES('$key', '$map')";
      if ($this->sql->exec($sqlStm)==0) {
        $sqlStm = "UPDATE `$tableMap` SET `map` = '$map' WHERE `key` = '$key'";
        $this->sql->exec($sqlStm);
      }
    } catch (\PDOException $e) {
      Log::getInstance()->e('DbError', $e->getMessage());
    }
  }

  //计算过期日期, 删除过期元组
  private function deleteExpiredRow() {
    try {
      $expireTime = intval(RECORD_LIVE) * 24;   //计算过期时间对应的小时
      $expireTime = '-'.strval($expireTime).' hour';
      $expireTime = date('Y-m-d', strtotime($expireTime, time()));

      //删除过期数据
      $sqlStm = "DELETE FROM `$this->table` WHERE `date` <= '$expireTime'";
      $this->sql->exec($sqlStm);
    } catch (\PDOException $e) {
      Log::getInstance()->e('DbError', $e->getMessage());
    }
  }

  //更改表结构, 删除一列
  private function removeCol($key) {
    try {
      $sqlStm = "ALTER TABLE `$this->table` DROP COLUMN `$key`";
      $this->sql->exec($sqlStm);

      $tableMap = $this->table.'_map';
      $sqlStm = "DELETE FROM `$tableMap` WHERE `key` = '$key'";
      $this->sql->exec($sqlStm);
    } catch (\PDOException $e) {
      Log::getInstance()->e('DbError', $e->getMessage());
    }
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

    try {
      //查询各字段的最大值
      $sqlStm = "SELECT $lists FROM `$this->table`";
      $result = $this->sql->query($sqlStm);
      if ($result!=null) {
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        $data = $result->fetchAll();
        foreach ($data[0] as $key => $value) {
          if (intval($value)==0) {    //40天内使用次数最大为0
            $this->removeCol($key);   //删除该列
          }
        }
      }
    } catch (\PDOException $e) {
      Log::getInstance()->e('DbError', $e->getMessage());
    }
  }

  //获取所有列字段名
  private function getAllList() {
    try {
      $tableMap = $this->table.'_map';
      $sqlStm = "SELECT `key` FROM `$tableMap`";
      $result = $this->sql->query($sqlStm);
      if ($result!=null) {
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        return $result->fetchAll();
      } else {
        return false;
      }
    } catch (\PDOException $e) {
      Log::getInstance()->e('DbError', $e->getMessage());
      return false;
    }
  }
}
?>