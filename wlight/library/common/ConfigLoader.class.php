<?php
/**
 * 数据库配置项加载管理
 * @author  KavMors(kavmors@163.com)
 *
 * void init(string, string)
 * string get(string, string)
 * boolean set(string, string, string)
 */

namespace wlight\common;
use wlight\util\DbHelper;

include_once (DIR_ROOT.'/wlight/library/util/DbHelper.class.php');

class ConfigLoader {
  private $sql;
  private $table;

  /**
   * @throws PDOException
   */
  public function __construct() {
    try {
      $helper = new \wlight\util\DbHelper();
      $this->sql = $helper->getConnector();
    } catch (\PDOException $e) {
      throw $e;
    }
  }

  /**
   * 初始化,设置模块名
   * @param string $module 模块名
   * @param string $tag 配置行标识
   * @throws PDOException
   */
  public function init($module, $tag) {
    $this->table = 'config_'.$module;
    $this->tag = $tag;

    //检查表存在性
    $collation = DB_COLLATION;
    $sqlStm = "CREATE TABLE IF NOT EXISTS `$this->table` (
                `name` char(10) COLLATE $collation NOT NULL COMMENT '配置行标识' PRIMARY KEY
              )";
    $this->sql->exec($sqlStm);

    //检查配置行存在性
    try {
      $sqlStm = "INSERT INTO `$this->table` (`name`) VALUES(?)";
      $result = $this->sql->prepare($sqlStm);
      $result = $result->execute(array($tag));
    } catch (\PDOException $e) {
      //ignore,可能配置行已存在
    }
  }

  /**
   * 获取配置值
   * @param string $field 配置项
   * @param string $default 默认值,默认null
   * @param string 配置值
   * @throws PDOException
   */
  public function get($field, $default=null) {
    if ($this->table == null || $this->tag == null) {
      return $default;
    }
    try {
      $sqlStm = "SELECT * FROM `$this->table` WHERE `name` = ?";
      $result = $this->sql->prepare($sqlStm);
      $result->execute(array($this->tag));
      $result = $result->fetchAll(\PDO::FETCH_ASSOC);
      if (count($result) == 0) {
        return $default;
      }
      return isset($result[0][$field]) ? $result[0][$field] : $default;
    } catch (\PDOException $e) {
      throw $e;
      return '';
    }
  }

  /**
   * 设置配置项
   * @param string $field 配置项
   * @param string $value 配置值
   * @param string $comment 配置备注,可选
   * @return boolean 成功则返回true
   * @throws PDOException
   */
  public function set($field, $value, $comment='') {
    if ($this->table == null || $this->tag == null) {
      return false;
    }
    try {
      $this->sql->beginTransaction();
      //检查表结构
      $sqlStm = "DESC `$this->table` `$field`";
      $result = $this->sql->query($sqlStm);
      if ($result==null || count($result->fetchAll(\PDO::FETCH_ASSOC))==0) {    //配置列不存在
        $this->appendCol($field, $comment);
      }

      $sqlStm = "UPDATE `$this->table` SET `$field` = ? WHERE `name` = ?";
      $result = $this->sql->prepare($sqlStm);
      $result->execute(array($value, $this->tag));

      $this->sql->commit();
      return true;
    } catch (\PDOException $e) {
      $this->sql->rollBack();
      throw $e;
      return false;
    }
  }

  private function appendCol($field, $comment='') {
    $collation = DB_COLLATION;
    $sqlStm = "ALTER TABLE `$this->table` ADD `$field` text DEFAULT '' COLLATE $collation COMMENT '$comment'";
    $this->sql->exec($sqlStm);
  }
}
?>