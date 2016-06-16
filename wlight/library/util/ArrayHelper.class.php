<?php
/**
 * 数组处理辅助类
 * @author  KavMors(kavmors@163.com)
 * @since   2.1
 */

namespace wlight\util;

class ArrayHelper {

  /**
   * 递归对数据每个元素作urlencode
   * @param array $arr - 数组
   * @return array - 处理后的数组
   */
  public function recursionUrlencode($arr) {
  	if ($arr === null) {
      return null;
    }
  	if (!is_array($arr)) {
      return urlencode($arr);
    }
  	foreach ($arr as $key => $value) {
  		$arr[$key] = self::recursionUrlencode($value);
  	}
  	return $arr;
  }

  /**
   * 数组转换为json格式,避免urlencode
   * @param array $arr - 数组
   * @return string - json字符串
   */
  public function toJson($arr) {
    return urldecode(json_encode(self::recursionUrlencode($arr)));
  }

  /**
   * 解析json字符串
   * @param string $json - json字符串
   * @return array - 解析后的数组
   */
  public function fromJson($json) {
    return json_decode($json, true);
  }

  /**
   * 数组转换为xml格式
   * @param array $arr - 数组
   * @return string - xml字符串
   */
  public function toXml($arr, $root = 'xml') {
    $xml = simplexml_load_string("<$root />");
    self::createXml(self::recursionUrlencode($arr), $xml);
    return urldecode($xml->saveXML());
  }

  /**
   * 解析xml字符串
   * @param string $xml - xml字符串
   * @return array - 解析后的数组
   */
  public function fromXml($xml) {
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    return json_decode(json_encode($obj), true);
  }

  private function createXml($arr, $node) {
    foreach ($arr as $k => $v) {
      if (is_array($v)) {
        $x = $node->addChild($k);
        self::createXml($v, $x);
      } else $node->addChild($k, $v);
    }
  }
}
?>