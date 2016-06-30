<?php
/**
 * Memcache操作辅助类
 * @author  KavMors(kavmors@163.com)
 * @since   2.3
 */

namespace wlight\util;

class MemcacheHelper {
  private $link;

  private $host;
  private $port;

  private $errorMsg;

  //可设置变量
  const HOST = 'host';
  const PORT = 'port';

  /**
   * 获取数据库连接对象
   * @return object - PDO连接对象
   */
  public function getConnector() {
    if ($this->link == null) {
      $this->checkConfig();
      $this->link = new \Memcache;
      if ($this->link->connect($this->host, $this->port)) {
        return $this->link;
      } else {
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
   * 重置所有配置为原始定义的值
   */
  public function loadDefault() {
    $this->host       = MEMCACHE_HOST;
    $this->port       = MEMCACHE_PORT;
  }

  //返回连接错误信息
  public function getError() {
    return $this->errorMsg;
  }

  //检查配置完整性
  private function checkConfig() {
    $this->host !== null       or $this->host      = MEMCACHE_HOST;
    $this->port !== null       or $this->port      = MEMCACHE_PORT;
  }
}
?>