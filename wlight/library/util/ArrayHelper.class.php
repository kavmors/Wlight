<?php
/**
 * 数组处理辅助类
 * @author  KavMors(kavmors@163.com)
 *
 * array recursionUrlencode(array)
 * string toJson(array)
 * array fromJson(string)
 * string toXml(array)
 * array fromXml(string)
 */

namespace wlight\util;

class ArrayHelper {

  /**
   * 递归对数据每个元素作urlencode
   * @param array $arr 数组
   * @return array 处理后的数组
   */
  public function recursionUrlencode($arr) {
    if ($arr === null) {
      return null;
    }
    if (!is_array($arr)) {
      return urlencode($arr);
    }
    foreach ($arr as $key => $value) {
      $arr[$key] = $this->recursionUrlencode($value);
    }
    return $arr;
  }

  /**
   * 数组转换为json格式,避免urlencode
   * @param array $arr 数组
   * @return string json字符串
   */
  public function toJson($arr) {
    if (defined('JSON_UNESCAPED_UNICODE')) {
      return json_encode($arr, JSON_UNESCAPED_UNICODE);
    } else {
      return urldecode(json_encode($this->recursionUrlencode($arr)));
    }
  }

  /**
   * 解析json字符串
   * @param string $json json字符串
   * @return array 解析后的数组
   */
  public function fromJson($json) {
    return json_decode($json, true);
  }

  /**
   * 数组转换为xml格式
   * @param array $arr 数组
   * @return string xml字符串
   * @throws DOMException
   */
  public function toXml($arr, $root = 'xml') {
    $dom = $this->createXml($this->recursionUrlencode($arr), $root);
    return urldecode($dom->saveXML());
  }

  /**
   * 解析xml字符串
   * @param string $xml xml字符串
   * @return array 解析后的数组
   */
  public function fromXml($xml) {
    $obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    return json_decode(json_encode($obj), true);
  }

  private function createXml($arr, $root, $dom = 0, $item = 0) {
    if (!$dom) {
      $dom = new \DOMDocument("1.0");
    }
    if(!$item) {
      $item = $dom->createElement($root);
      $dom->appendChild($item);
    }

    foreach ($arr as $k => $v) {
      $itemx = $dom->createElement(is_string($k) ? $k : "item");
      $item->appendChild($itemx);
      if (!is_array($v)) {
        $text = $dom->createTextNode($v);
        $itemx->appendChild($text);
      } else {
        $this->createXml($v, $root, $dom, $itemx);
      }
    }
    return $dom;
  }
}
?>