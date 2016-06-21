<?php
/**
 * Sample file
 */
namespace wlight\msg;
use wlight\core\Response;

//类名与文件名的主体部分相同
//详细规则参考readme
class Sample extends Response {
  public function verify() {
    //返回true时执行invoke方法
    return $this->map['Content'] == 'hello';
  }

  public function invoke() {
    //回复内容
    return $this->makeText('some text to reply...');
  }

  public function cache() {
    return true;    //缓存控制
  }

  public function tag() {
    return 'Just a sample';
  }
}

?>