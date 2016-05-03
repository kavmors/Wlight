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
    include_once (DIR_ROOT.'/wlight/library/api/basic/AccessToken.class.php');
    include_once (DIR_ROOT.'/wlight/library/util/HttpClient.class.php');
    include_once (DIR_ROOT.'/wlight/library/runtime/ApiException.class.php');

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
    $data = $httpClient->jsonToArray();

    if (isset($data['ip_list'])) {
      return $data['ip_list'];
    } else {
      throw ApiException::errorJsonException('response: '.$httpClient->getResponse());
    }

    //never
    return false;
  }
}
?>