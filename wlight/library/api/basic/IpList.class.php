<?php
/**
 * 获取微信服务器IP地址
 * http://mp.weixin.qq.com/wiki/0/2ad4b6bfd29f30f71d39616c2a0fcedc.html
 * @author  KavMors(kavmors@163.com)
 *
 * array get()
 */

namespace wlight\basic;
use wlight\util\HttpClient;
use wlight\runtime\ApiException;

include_once (DIR_ROOT.'/wlight/library/api/basic/AccessToken.class.php');
include_once (DIR_ROOT.'/wlight/library/util/HttpClient.class.php');
include_once (DIR_ROOT.'/wlight/library/runtime/ApiException.class.php');

class IpList {
  private $url = 'https://api.weixin.qq.com/cgi-bin/getcallbackip';

  /**
   * @throws ApiException
   */
  public function __construct() {
    $accessToken = new AccessToken();
    $accessToken = $accessToken->get();
    $this->url .= "?access_token=$accessToken";
  }

  /**
   * 获取IP列表
   * @return array 对应ip地址数组(请求失败返回false)
   * @throws ApiException
   */
  public function get() {
    $httpClient = new HttpClient($this->url);
    $httpClient->get();
    $data = $httpClient->jsonToArray();

    if (isset($data['ip_list'])) {
      return $data['ip_list'];
    } else {
      throw ApiException::throws(ApiException::ERROR_JSON_ERROR_CODE, 'response: '.$httpClient->getResponse());
    }

    //never
    return false;
  }
}
?>