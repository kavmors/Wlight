<?php
/**
 * 获取用户基本信息
 * http://mp.weixin.qq.com/wiki/14/bb5031008f1494a59c6f71fa0f319c66.html
 * http://mp.weixin.qq.com/wiki/0/d0e07720fc711c02a3eab6ec33054804.html
 * http://mp.weixin.qq.com/wiki/1/4a566d20d67def0b3c1afc55121d2419.html
 * @author  KavMors(kavmors@163.com)
 *
 * array get(string/array, string)
 * array getUserListFromStart()
 * array getUserList(string)
 * boolean setRemark(string, string)
 */

namespace wlight\user;
use wlight\basic\AccessToken;
use wlight\util\HttpClient;
use wlight\runtime\ApiException;

include_once (DIR_ROOT.'/wlight/library/api/basic/AccessToken.class.php');
include_once (DIR_ROOT.'/wlight/library/util/HttpClient.class.php');
include_once (DIR_ROOT.'/wlight/library/runtime/ApiException.class.php');

class Info {
  const LIST_MAX = 10000;

  private $url = 'https://api.weixin.qq.com/cgi-bin/user';
  private $accessToken;
  private $nextOpenId='';

  /**
   * @throws ApiException
   */
  public function __construct() {
    $accessToken = new AccessToken();
    $this->accessToken = $accessToken->get();
  }

  /**
   * 获取用户信息
   * @param string/array $openId 用户openid列表数组(不超过100个)
   * @param string $language 可选,语言版本(zh_CN, zh_TW, en)
   * @return array 用户信息列表数组(请求失败返回false)
   * @throws ApiException
   */
  public function get($openId, $language = 'zh_CN') {
    if (is_string($openId)) {
      $openId = array($openId);
    }
    //组成json数组格式
    $openidFormat = array();
    foreach ($openId as $id) {
      $openidFormat[] = array(
        'openid' => $id,
        'lang' => $language
      );
    }

    $json = array(
      'user_list' => $openidFormat
    );
    $url = $this->url.'/info/batchget?access_token='.$this->accessToken;
    $httpClient = new HttpClient($url);
    $httpClient->setBody(json_encode($json));
    $httpClient->post();
    $result = $httpClient->jsonToArray();

    if (isset($result['user_info_list'])) {
      return $result['user_info_list'];
    } else {
      throw ApiException::throws(ApiException::ERROR_JSON_ERROR_CODE, 'response: '.$httpClient->getResponse());
    }
    return false;
  }

  /**
   * 从头获取用户的openid列表(最多拉取10000个)
   * @return array 接口返回结果集合,包含总关注数、本次拉取数及openid列表
   * @throws ApiException
   */
  public function getUserListFromStart() {
    $this->nextOpenId = '';
    return $this->getUserList();
  }

  /**
   * 获取用户的openid列表(每次最多拉取10000个)
   * @param string $fromOpenId 起始openid,不填写代表接上次结果继续拉取
   * @return array 接口返回结果集合,包含总关注数、本次拉取数及openid列表
   * @throws ApiException
   */
  public function getUserList($fromOpenId = '') {
    if (empty($fromOpenId)) {
      $fromOpenId = $this->nextOpenId;
    }
    $url = $this->url.'/get?access_token='.$this->accessToken.'&next_openid='.$fromOpenId;
    $httpClient = new HttpClient($url);
    $httpClient->get();
    $result = $httpClient->jsonToArray();

    if (isset($result['total'])) {
      $this->nextOpenId = $result['next_openid'];
      if ($result['count']<self::LIST_MAX) {    //拉取完毕
        $this->nextOpenId = '';
      }
      return $result['data']['openid'];
    } else {
      throw ApiException::throws(ApiException::ERROR_JSON_ERROR_CODE, 'response: '.$httpClient->getResponse());
    }
    return false;
  }

  /**
   * 设置用户备注名
   * @param string $openId 用户openid
   * @param string $remark 备注名,小于30字符
   * @return boolean 设置成功返回true
   * @throws ApiException
   */
  public function setRemark($openId, $remark) {
    $url = $this->url.'/info/updateremark?access_token='.$this->accessToken;
    $json = array(
      'openid' => $openId,
      'remark' => urlencode($remark)
    );
    $httpClient = new HttpClient($url);
    $httpClient->setBody(urldecode(json_encode($json)));
    $httpClient->post();
    $result = $httpClient->jsonToArray();

    if (isset($result['errcode']) && $result['errcode']==0) {
      return true;
    } else {
      throw ApiException::throws(ApiException::ERROR_JSON_ERROR_CODE, 'response: '.$httpClient->getResponse());
    }
  }
}
?>