<?php
/**
 * 可供外部应用导入框架
 * @author  KavMors(kavmors@163.com)
 */

namespace wlight\dev;

/********** 加载config.json ****************/

if (!defined('WLIGHT')) {
  $file = dirname(__FILE__).'/../../runtime/cache/config.json.php';
  if (!file_exists($file)) {
    die('配置文件丢失:( 请重新配置Wlight');
  }
  $config = file_get_contents($file);
  $config = trim(strstr($config, '{'));
  $config = json_decode($config, true);
  if (!$config) {
    die('配置文件无效:( 请重新配置Wlight');
  }
  foreach ($config as $k => $v) {
    define ($k, $v);
  }
  //execute config
  date_default_timezone_set(DEFAULT_TIMEZONE);
}

class Library {
  /**
   * 引入一个类库文件,并返回该类实例对象
   * @param string $namespace 命名空间
   * @param string $className 类名
   * @return 实例对象,类名不合法则返回null
  */
  public static function import($namespace, $className) {
    $wholeClass = "\\wlight\\$namespace\\$className";
    if ($namespace != 'util' && $namespace != 'common') {
      $namespace = "api/$namespace";
    }

    include_once (DIR_ROOT."/wlight/library/$namespace/$className.class.php");
    $instance = new $wholeClass;
    return $instance;
  }
}
?>