<?php
/**
 * 网页授权获取用户信息的接口
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\web;

class Oauth {
  private $redirectUrl;
  private $appId;
  private $webRoot;

	public function __construct() {
    $this->appId = APP_ID;
    $this->webRoot = HOST.PATH;
  }

  /**
   * 设置回调后重定向url
   * @param string $redirectUrl - 重定向url
   */
  public function setRedirectUrl($redirectUrl) {
    $this->redirectUrl = $redirectUrl;
  }

  /**
   * 获取scope为snsapi_basic的重定向路径(只能获取openId)
   * @param string $extraString - 可选,开发者额外参数
   * @return string - 重定向路径
   */
  public function getBasic($extraString = '') {
    return $this->getLocation('snsapi_base', $extraString);
  }

  /**
   * 获取scope为snsapi_userinfo的重定向路径(获取用户具体信息)
   * @param string $extraString - 可选,开发者额外参数
   * @return string - 重定向路径
   */
  public function getUserInfo($extraString = '') {
    return $this->getLocation('snsapi_userinfo', $extraString);
  }

  private function getLocation($scope, $extraString) {
    $redirect_uri = urlencode($this->redirectUrl);
    $extraString = urlencode($extraString);
    $location = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->appId&redirect_uri=$redirect_uri&response_type=code&scope=$scope&state=$extraString#wechat_redirect";
    return $location;
  }
}
?>