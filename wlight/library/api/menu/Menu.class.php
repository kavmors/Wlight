<?php
/**
 * 自定义菜单开发接口(默认菜单)
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\menu;
use wlight\basic\AccessToken;
use wlight\util\HttpClient;
use wlight\runtime\ApiException;

class Menu {
  private $url = 'https://api.weixin.qq.com/cgi-bin/menu';
  private $accessToken;

  /**
   * @throws ApiException
   */
  public function __construct() {
    include_once (DIR_ROOT.'/wlight/library/api/basic/AccessToken.class.php');
    include_once (DIR_ROOT.'/wlight/library/util/HttpClient.class.php');
    include_once (DIR_ROOT.'/wlight/library/runtime/ApiException.class.php');

    $accessToken = new AccessToken();
    $this->accessToken = $accessToken->get();
  }

  /**
   * 创建自定义菜单(默认或个性化菜单)
   * @param array $menu - 自定义菜单内容数组
   * @param array $condition - 可选, 个性化菜单的用户组条件, 不填则创建默认菜单
   * @return boolean/string - 创建默认菜单时,成功返回true;创建个性化菜单时,成功返回menuid
   * @throws ApiException
   */
  public function create($menu, $condition=null) {
    if (is_array($menu) || !isset($menu['button'])) {
      $menu = array('button'=>$menu);
    } else {
      return false;
    }
    if (is_array($condition)) {
      $url = $this->url.'/addconditional?access_token='.$this->accessToken;
      $menu['matchrule'] = $condition;
    } else {
      $url = $this->url.'/create?access_token='.$this->accessToken;
    }
    $httpClient = new HttpClient($url);
    $httpClient->setBody(urldecode(json_encode($this->arrUrlencode($menu))));
    $httpClient->post();
    $result = $httpClient->jsonToArray();

    if (isset($result['menuid'])) {
      return $result['menuid'];
    } else {
      throw ApiException::errorJsonException('response: '.$httpClient->getResponse());
    }

    //never
    return false;
  }

  /**
   * 查询自定义菜单(结果包含默认和个性化菜单)
   * @param boolean $assocArray - 可选,false则直接返回API的结果(默认true返回解析后的数组)
   * @return string/array - 查询后的结果
   * @throws ApiException
   */
  public function get($assocArray = true) {
    $url = $this->url.'/get?access_token='.$this->accessToken;
    $httpClient = new HttpClient($url);
    $httpClient->get();

    if ($httpClient->getStatus()!=200 || empty($httpClient->getResponse())) {
      throw ApiException::httpException('status code: '.$httpClient->getStatus());
      return false;
    }
    if (!$assocArray) {     //直接返回API接口的结果
      return $httpClient->getResponse();
    }

    $result = $httpClient->jsonToArray();
    return $result;
  }

  /**
   * 删除自定义菜单(默认或个性化)
   * @param string $menuId - 可选,个性化菜单的menuid,不填则删除所有菜单(包括默认和个性化)
   * @return boolean - 删除成功时返回true
   * @throws ApiException
   */
  public function delete($menuId=null) {
    if (is_string($menuId)) {
      $url = $this->url.'/delconditional?access_token='.$this->accessToken;
      $httpClient = new HttpClient($url);
      $httpClient->setBody(json_encode(array('menuid'=>$menuId)));
      $httpClient->post();
    } else {
      $url = $this->url.'/delete?access_token='.$this->accessToken;
      $httpClient = new HttpClient($url);
      $httpClient->get();
    }
    $result = $httpClient->jsonToArray();

    if (isset($result['errcode'] && $result['errcode']==0)) {
      return true;
    }

    //never
    return false;
  }

  /**
   * 测试个性化菜单
   * @param string $userId - 用户openId或微信号
   * @param boolean $assocArray - 可选,false则直接返回API的结果(默认true返回解析后的数组)
   * @return string/array - 查询后的结果
   */
  public function test($userId, $assocArray=true) {
    $url = $this->url.'/trymatch?access_token='.$this->accessToken;
    $httpClient = new HttpClient($url);
    $httpClient->setBody(json_encode(array('user_id'=>$userId)));
    $httpClient->post();

    if ($httpClient->getStatus()!=200 || empty($httpClient->getResponse())) {
      throw ApiException::httpException('status code: '.$httpClient->getStatus());
      return false;
    }
    if (!$assocArray) {     //直接返回API接口的结果
      return $httpClient->getResponse();
    }

    $result = $httpClient->jsonToArray();
    return $result;
  }

  //递归将数组每个元素执行urlencode
  private function arrUrlencode($arr) {
    if ($arr===null) {
      return null;
    }
    if (!is_array($arr)) {
      return urlencode($arr);
    }
    foreach ($arr as $key => $value) {
      $arr[$key] = $this->arrUrlencode($value);
    }
    return $arr;
  }
}
?>