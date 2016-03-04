<?php
/**
 * 供外部应用调用框架类所用
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\dev;

include (dirname(__FILE__).'/Config.class.php');

class Library {
  private static $root;

  /**
   * 引入一个类库文件,并返回该类的完整类名(包含命名空间)
   * @param $namespace - 类所在的空间
   * @param $className - 类名
   * @return 完整的命名空间+类名
   * @example 引用AccessToken类时:
   *          $class = Library::import('basic', 'AccessToken');
   *          $accessToken = (new $class())->get()
   */
  public static function import($namespace, $className) {
    self::checkRoot();
    $wholeClass = "\\wlight\\$namespace\\$className";
    //除了util外,其余类库在/api内
    if ($namespace!='util') {
      $namespace = "api/$namespace";
    }
    include_once(self::$root."/wlight/library/$namespace/$className.class.php");
    $instance = new $wholeClass();
    return $instance;
  }

  //检查$root初始化
  private static function checkRoot() {
    if (!self::$root) {
      self::$root = Config::get('DIR_ROOT');
    }
  }
}
?>