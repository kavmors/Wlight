<?php
/**
 * 数据库操作辅助类
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\util;

class DbHelper {
  private $link;
  private $configJson;

  private $charset;
  private $collation;
  private $type;
  private $host;
  private $port;
  private $dbname;
  private $user;
  private $pwd;

  private $errorMsg;

  //可设置变量
  const CHARSET = 'charset';
  const COLLATION = 'collation';
  const TYPE = 'type';
  const HOST = 'host';
  const PORT = 'port';
  const DBNAME = 'dbname';
  const USER = 'user';
  const PWD = 'pwd';

  public function __construct() {
    if (defined('DB_HOST')) {
      $this->configJson = null;
    } else {
      $this->configJson = \wlight\dev\Config::getAll();
    }
  }

  /**
   * 选择数据库
   * @param string $dbName - 数据库名称
   */
  public function selectDatabase($dbName) {
    $this->set(self::DBNAME, $dbName);
  }

  /**
   * 获取数据库连接对象
   * @return object - PDO连接对象
   */
  public function getConnector() {
    if ($this->link==null) {
      $this->checkConfig();
      try {
        $statement = "%s:host=%s;port=%s;dbname=%s";
        $statement = sprintf($statement, $this->type, $this->host, $this->port, $this->dbname);
        $this->link = new \PDO($statement, $this->user, $this->pwd);
        $this->link->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->link->exec("set names ".$this->charset);
        $this->link->exec("set time_zone = '+8:00'");
      } catch (\PDOException $e) {
        $this->errorMsg = $e->getMessage();
        return null;
      }
    }
    return $this->link;
  }

  /**
   * 重新链接数据库
   * @return object - PDO连接对象
   */
  public function reconnect() {
    $this->link = null;
    return $this->getConnector();
  }

  /**
   * 设置配置参数
   * @param string $key - 参数的变量名,从本类常量中选取
   * @param string $configValue - 配置参数的值
   */
  public function set($key, $configValue) {
    $this->$key = $configValue;
  }

  /**
   * 获取配置参数
   * @param string $key - 参数的变量名,从本类常量中选取
   * @return string - 配置当前值
   */
  public function get($key) {
    return $this->$key;
  }

  /**
   * 重置所有配置, 全部使用配置文件中定义的值
   */
  public function loadDefault() {
    if ($this->configJson==null) {
      $this->charset    = DB_CHARSET;
      $this->collation  = DB_COLLATION;
      $this->type       = DB_TYPE;
      $this->host       = DB_HOST;
      $this->port       = DB_PORT;
      $this->dbname     = DB_NAME;
      $this->user       = DB_USER;
      $this->pwd        = DB_PWD;
    } else {
      $this->charset    = $this->configJson['DB_CHARSET'];
      $this->collation  = $this->configJson['DB_COLLATION'];
      $this->type       = $this->configJson['DB_TYPE'];
      $this->host       = $this->configJson['DB_HOST'];
      $this->port       = $this->configJson['DB_PORT'];
      $this->dbname     = $this->configJson['DB_NAME'];
      $this->user       = $this->configJson['DB_USER'];
      $this->pwd        = $this->configJson['DB_PWD'];
    }
  }

  //返回连接错误信息
  public function getError() {
    return $this->errorMsg;
  }

  //检查配置完整性
  private function checkConfig() {
    if ($this->configJson==null) {
      $this->charset !== null    or $this->charset   = DB_CHARSET;
      $this->collation !== null  or $this->collation = DB_COLLATION;
      $this->type !== null       or $this->type      = DB_TYPE;
      $this->host !== null       or $this->host      = DB_HOST;
      $this->port !== null       or $this->port      = DB_PORT;
      $this->dbname !== null     or $this->dbname    = DB_NAME;
      $this->user !== null       or $this->user      = DB_USER;
      $this->pwd !== null        or $this->pwd       = DB_PWD;
    } else {
      $this->charset !== null    or $this->charset   = $this->configJson['DB_CHARSET'];
      $this->collation !== null  or $this->collation = $this->configJson['DB_COLLATION'];
      $this->type !== null       or $this->type      = $this->configJson['DB_TYPE'];
      $this->host !== null       or $this->host      = $this->configJson['DB_HOST'];
      $this->port !== null       or $this->port      = $this->configJson['DB_PORT'];
      $this->dbname !== null     or $this->dbname    = $this->configJson['DB_NAME'];
      $this->user !== null       or $this->user      = $this->configJson['DB_USER'];
      $this->pwd !== null        or $this->pwd       = $this->configJson['DB_PWD'];
    }
  }
}
?>