<?php
/**
 * 网页授权的回调处理脚本(非框架接口)
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\web;
use wlight\util\HttpClient;
use wlight\runtime\ApiException;

class OauthRedirect {
  private $code;
  private $state;
  private $appid;
  private $appsecret;
  private $accessToken;
  private $openid;

  /**
   * @throws ApiException
   */
  public function __construct() {
    include_once (DIR_ROOT.'/wlight/library/util/HttpClient.class.php');
    include_once (DIR_ROOT.'/wlight/library/runtime/ApiException.class.php');

    if (!isset($_GET['state'])) {
      exit;
    }
    if (!isset($_GET['code'])) {
      throw new ApiException(ApiException::OAUTH_REJECT_ERROR_MSG, ApiException::OAUTH_REJECT_ERROR_CODE, 'state: '.$_GET['state']);
      return;
    }
    $this->code = $_GET['code'];
    $this->state = $_GET['state'];
    $this->appid = APP_ID;
    $this->appsecret = APP_SECRET;
  }

  /**
   * 获取基本信息(access_token及openid)
   * @return array - 基本信息数组
   */
  public function getBasic() {
    $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appid&secret=$this->appsecret&code=$this->code&grant_type=authorization_code";
    $httpClient = new HttpClient($url);
    $httpClient->get();
    $result = $this->checkErrcode($httpClient);
    if (isset($result['openid'])) {
      $this->accessToken = $result['access_token'];
      $this->openid = $result['openid'];
      return $result;
    } else {
      throw ApiException::errorJsonException('response: '.$httpClient->getResponse());
    }
    return false;
  }

  /**
   * 获取用户详细信息
   * @param string $language - 可选,用户语言版本
   * @return array - 详细信息数组
   */
  public function getUserInfo($language='zh_CN') {
    if (!$this->openid) {
      $this->getBasic();
    }

    $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$this->accessToken&openid=$this->openid&lang=$language";
    $httpClient = new HttpClient($url);
    $httpClient->get();
    $result = $this->checkErrcode($httpClient);
    return $result;
  }

  private function checkErrcode($httpClient) {
    $result = $httpClient->jsonToArray();
    return $result;
  }
}
?>