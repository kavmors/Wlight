<?php
/**
 * 获取基本配置参数(外部应用调用)
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\dev;

class Config {
  //获取名为$config的配置参数
  public static function get($config) {
    $file = dirname(__FILE__).'/../../runtime/cache/config.json.php';
    if (file_exists($file)) {
      $json = json_decode(self::getConfigStr($file), true);
      if (!$json) {
        return null;
      }
      return isset($json[$config])? $json[$config]: null;
    }
    return null;
  }

  //获取所有配置参数(Array返回)
  public static function getAll() {
    $file = dirname(__FILE__).'/../../runtime/cache/config.json.php';
    if (file_exists($file)) {
      $json = json_decode(self::getConfigStr($file), true);
      if (!$json) {
        return null;
      } else {
        return $json;
      }
    }
    return null;
  }

  //取Config文件内容, 并解析成json字符串
  private static function getConfigStr($file) {
    $content = file_get_contents($file);
    $start = stripos($content, '?>') + 2;
    return substr($content, $start);
  }
}
?>