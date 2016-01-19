<?php
/**
 * 数据库初始化
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\core;
use \wlight\util\DbHelper;

include_once (DIR_ROOT.'/wlight/library/util/DbHelper.class.php');

$user = DB_USER;
$host = DB_HOST;
$dbname = DB_NAME;
$collation = DB_COLLATION;
$table = DB_PREFIX.'_tag';

// Connect
$helper = new DbHelper();
$helper->set(DbHelper::TYPE, 'mysql');
$helper->set(DbHelper::DBNAME, '');      //首次操作不指定数据库
$link = $helper->getConnector();

if ($link==null) {
  die('Failed to connect database server.');
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
  die("Permission denied in $dbname. Create by root and check permissions of '$user'@'$host'.");
}

// Create table
try {
  $link->exec("CREATE TABLE IF NOT EXISTS `$table` (
    `date` date COLLATE $collation NOT NULL,
    PRIMARY KEY(`date`)
  )");

  $tableMap = $table.'_map';
  $link->exec("CREATE TABLE IF NOT EXISTS `$tableMap` (
    `key` char(20) COLLATE $collation NOT NULL,
    `map` varchar(30) COLLATE $collation NOT NULL,
    PRIMARY KEY(`key`)
  )");
  $link = null;
} catch (\PDOException $e) {
  $link = null;
  die('Permission denied in creating table.');
}
?>