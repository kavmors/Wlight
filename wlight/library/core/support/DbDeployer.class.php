<?php
/**
 * 数据库初始化
 * @author  KavMors(kavmors@163.com)
 */

namespace wlight\core\support;
use \wlight\util\DbHelper;

class DbDeployer {
  private static $tag;
  private static $map;
  private static $message;
  private static $cacheMsg;
  private static $cacheRetry;
  private static $cacheOauth;

  public static function init() {
    self::$tag = DB_PREFIX.'_statis_tag';
    self::$map = self::$tag.'_map';
    self::$message = DB_PREFIX.'_statis_message';
    self::$cacheMsg = DB_PREFIX.'_cache_msg';
    self::$cacheRetry = DB_PREFIX.'_cache_retry';
    self::$cacheOauth = DB_PREFIX.'_cache_oauth';
  }

  public static function initMysql() {
    $user = DB_USER;
    $host = DB_HOST;
    $dbname = DB_NAME;
    $collation = DB_COLLATION;
    $tag = self::$tag;
    $map = self::$map;
    $message = self::$message;
    $cacheMsg = self::$cacheMsg;
    $cacheRetry = self::$cacheRetry;
    $cacheOauth = self::$cacheOauth;

    // Connect
    try {
    	$helper = new DbHelper();
    	$helper->set(DbHelper::TYPE, 'mysql');
    	$helper->set(DbHelper::DBNAME, '');      //首次操作不指定数据库
    	$link = $helper->getConnector();
    } catch (\PDOException $e) {
    	echo 'Failed to connect database server.';
      return false;
    }

    if ($link==null) {
      echo 'Failed to connect database server.';
      return false;
    }

    // Create db
    try {
      $link->exec("CREATE DATABASE IF NOT EXISTS `$dbname` COLLATE $collation");
    } catch (\PDOException $ignored) {
      //do nothing: permission denied in creating database
    }

    // Select db
    try {
      $link->exec("USE `$dbname`");
    } catch (\PDOException $e) {
      $link = null;
      $msg = $e->getMessage();
      echo "Permission denied in $dbname. Create by root and check permissions of '$user'@'$host'.($msg)";
      return false;
    }

    // Create table
    try {
      $link->exec("CREATE TABLE IF NOT EXISTS `$tag` (
        `date` date COLLATE $collation NOT NULL COMMENT '日期',
        PRIMARY KEY(`date`)
      ) COMMENT='功能统计'");

      $link->exec("CREATE TABLE IF NOT EXISTS `$map` (
        `key` varchar(50) COLLATE $collation NOT NULL,
        `map` varchar(50) COLLATE $collation NOT NULL,
        PRIMARY KEY(`key`)
      ) COMMENT='功能描述'");

      $link->exec("CREATE TABLE IF NOT EXISTS `$message` (
          `wechat_id` char(30) COLLATE $collation NOT NULL COMMENT 'FromUserName',
          `create_time` timestamp NOT NULL DEFAULT 0 COMMENT 'CreateTime',
          `msgType` varchar(10) COLLATE $collation NOT NULL DEFAULT 'text' COMMENT 'MsgType',
          `content` text COLLATE $collation NOT NULL DEFAULT '' COMMENT '留言消息',
          `extra` text COLLATE $collation NOT NULL DEFAULT '' COMMENT '额外信息',
          PRIMARY KEY(`wechat_id`, `create_time`)
      ) COMMENT='留言统计'");

      $link->exec("CREATE TABLE IF NOT EXISTS `$cacheMsg` (
        `key` varchar(50) COLLATE $collation NOT NULL COMMENT '关键字',
        `target` varchar(50) COLLATE $collation NOT NULL COMMENT '搜索对象',
        `type` char(10) COLLATE $collation NOT NULL COMMENT '消息类型',
        `priority` int NOT NULL DEFAULT 0 COMMENT '优先级',
        PRIMARY KEY(`key`, `type`)
      ) COMMENT='消息缓存'");

      $link->exec("CREATE TABLE IF NOT EXISTS `$cacheRetry` (
        `key` char(40) COLLATE $collation NOT NULL COMMENT '重排key',
        `reply` text COLLATE $collation NOT NULL DEFAULT '' COMMENT '回复内容',
        `priority` int NOT NULL DEFAULT 0 COMMENT '优先级',
        PRIMARY KEY(`key`)
      ) COMMENT='重试缓存'");

      $link->exec("CREATE TABLE IF NOT EXISTS `$cacheOauth` (
        `id` char(10) COLLATE $collation NOT NULL COMMENT '编号',
        `url` text COLLATE $collation NOT NULL DEFAULT '' COMMENT '重定向url',
        `extra` text COLLATE $collation NOT NULL DEFAULT '' COMMENT '额外信息',
        `result` text COLLATE $collation NOT NULL DEFAULT '' COMMENT '查询响应缓存',
        `priority` int NOT NULL DEFAULT 0 COMMENT '优先级',
        PRIMARY KEY(`id`)
      ) COMMENT='Oauth缓存'");

      $link = null;
      return true;
    } catch (\PDOException $e) {
      $link = null;
      $msg = $e->getMessage();
      echo "Permission denied in creating tables.($msg)";
      return false;
    }
  }

  public static function resetMysql() {
    $cacheMsg = self::$cacheMsg;
    $cacheRetry = self::$cacheRetry;
    $cacheOauth = self::$cacheOauth;
    $helper = new DbHelper();
    $helper->set(DbHelper::TYPE, 'mysql');
    $link = $helper->getConnector();

    $link->exec("DELETE FROM `$cacheMsg` WHERE 1");
    $link->exec("DELETE FROM `$cacheRetry` WHERE 1");
    $link->exec("DELETE FROM `$cacheOauth` WHERE 1");
  }
}
DbDeployer::init();
?>