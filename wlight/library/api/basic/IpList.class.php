<?php
/**
 * 获取微信服务器IP地址
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\basic;
use wlight\util\HttpClient;
use wlight\runtime\ApiException;

class IpList {
	private $url = 'https://api.weixin.qq.com/cgi-bin/getcallbackip';

  /**
   * @throws ApiException
   */
  public function __construct() {
    include_once (self::getDirRoot().'/wlight/library/api/basic/AccessToken.class.php');
    include_once (self::getDirRoot().'/wlight/library/util/HttpClient.class.php');
    include_once (self::getDirRoot().'/wlight/library/runtime/ApiException.class.php');

    $accessToken = new AccessToken();
    $accessToken = $accessToken->get();
    $this->url .= "?access_token=$accessToken";
  }

  /**
   * 获取IP列表
   * @return array - 对应ip地址数组(请求失败返回false)
   * @throws ApiException
   */
  public function get() {
    $httpClient = new HttpClient($this->url);
    $httpClient->get();
    if ($httpClient->getStatus()!=200 || empty($httpClient->getResponse())) {
      throw ApiException::httpException('status code: '.$httpClient->getStatus());
      return false;
    }

    $data = json_decode($httpClient->getResponse(), true);
    if (!$data) {
      throw ApiException::jsonDecodeException('response: '.$httpClient->getResponse());
      return false;
    }
    if (isset($data['ip_list'])) {
      return $data['ip_list'];
    } else {
      if (isset($data['errcode'])) {
        throw new ApiException($data['errmsg'], $data['errcode']);
      } else {
        throw ApiException::illegalJsonException('response: '.$httpClient->getResponse());
      }
    }
    return false;
  }

  //以下方法供外置应用调用本类时读取相关配置所用
  
  //获取项目根目录
  private static function getDirRoot() {
    return defined('DIR_ROOT')? DIR_ROOT: \wlight\dev\Config::get('DIR_ROOT');
  }
}
?>