<?php
/**
 * 提供自动回复前和回复执行后的监听接口
 * @author  KavMors(kavmors@163.com)
 * @version 3.0
 */

namespace wlight\msg;

class Hook {

  /**
   * 在所有自动回复前执行
   * @param array &$map - 执行前的数据对象(来自微信服务器)
   */
  public function onPreExecute(&$map) {
    //TODO: 在回复前执行..
  }

  /**
   * 在所有自动回复后执行
   * @param string &$result - 执行后的xml字符串(向微信服务器回复的内容)
   */
  public function onPostExecute(&$result) {
    //TODO: 在自动回复逻辑结束后执行..
  }
}

?>