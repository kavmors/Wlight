<?php
/**
 * 供外部应用调用框架类所用
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\dev;

require (dirname(__FILE__).'/Config.php');

class Library {
  /**
   * 引入一个类库文件,并返回该类实例对象
   * @param $namespace - 类所在的空间
   * @param $className - 类名
   * @return 实例对象
  */
  public static function import($namespace, $className) {
    $wholeClass = "\\wlight\\$namespace\\$className";
    //除了util外,其余类库在/api内
    if ($namespace!='util') {
      $namespace = "api/$namespace";
    }
    include_once(DIR_ROOT."/wlight/library/$namespace/$className.class.php");
    $instance = new $wholeClass();
    return $instance;
  }
}
?>