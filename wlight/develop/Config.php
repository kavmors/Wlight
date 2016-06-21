<?php
/**
 * 定义基本配置参数(外部应用调用)
 * @author  KavMors(kavmors@163.com)
 * @since   2.1
 */

namespace wlight\dev;

if (!defined('WLIGHT')) {
  Config::defineAll();
}

class Config {
  //定义所有配置常量
  public static function defineAll() {
    $config = self::getAll();
    foreach ($config as $key => $value) {
      defined($key) or define($key, $value);
    }
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