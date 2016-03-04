<?php
/**
 * 微信内网页开发功能(jssdk)开发类库
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\web;

class Jssdk {
  private $debug = 'false';
  private $appId;
  private $ticket;

  public function __construct() {
    include_once (self::getDirRoot().'/wlight/library/api/web/JsapiTicket.class.php');
    $this->appId = self::getAppId();
    $ticket = new JsapiTicket();
    $this->ticket = $ticket->get();
  }

  /**
   * 设置调试模式
   * @param boolean $debug - true为开启调试模式
   */
  public function setDebug($debug) {
    $this->debug = $debug? 'true': 'false';
  }

  /**
   * 获取jsapi接口的配置信息
   * @param string/array $apiList - 需要使用的JS接口列表
   * @return string - 验证配置对应的js语句,可直接在js脚本中使用
   */
  public function config($apiList) {
    if (is_string($apiList)) {
      $apiList = array($apiList);
    }
    $apiList = json_encode($apiList);
    $signPackage = $this->getSignPackage();
    $signPackage = (Object)$signPackage;
    $rt = "wx.config({
        debug: $this->debug,
        appId: '$signPackage->appId',
        timestamp: $signPackage->timestamp,
        nonceStr: '$signPackage->nonceStr',
        signature: '$signPackage->signature',
        jsApiList: $apiList
      });\n";
    return $rt;
  }

  /**
   * 获取引入js文件的路径
   * @param string $version - 可选,引入文件的版本号,默认1.0.0
   * @return string - js文件路径
   */
  public function getReference($version = '1.0.0') {
    return "https://res.wx.qq.com/open/js/jweixin-".$version.".js";
  }

  /**
   * 获取引入js文件的标签
   * @param string $version - 可选,引入文件的版本号,默认1.0.0
   * @return string - js文件标签
   */
  public function getReferenceLabel($version = '1.0.0') {
    $reference = $this->getReference($version);
    return "<script type='text/javascript' src='$reference'></script>\n";
  }

  /**
   * 获取权限签名
   * @return array - 权限签名数组,包含appId、signature等字段
   */
  public function getSignPackage() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $timestamp = time();
    $nonceStr = $this->createNonceStr();

    //rank by ascii
    $string = "jsapi_ticket=$this->ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
    $signature = sha1($string);
    $signPackage = array(
      'appId'     => $this->appId,
      'nonceStr'  => $nonceStr,
      'timestamp' => $timestamp,
      'url'       => $url,
      'signature' => $signature,
      'rawString' => $string
    );
    return $signPackage;
  }

  //生成随机字符串
  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

  //以下方法供外置应用调用本类时读取相关配置所用

  //获取项目根目录
  private static function getDirRoot() {
    return defined('DIR_ROOT')? DIR_ROOT: \wlight\dev\Config::get('DIR_ROOT');
  }

  //获取项目AppId
  private static function getAppId() {
    return defined('APP_ID')? APP_ID: \wlight\dev\Config::get('APP_ID');
  }
}
?>