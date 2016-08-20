<?php
/**
 * 用户分组管理
 * http://mp.weixin.qq.com/wiki/0/56d992c605a97245eb7e617854b169fc.html
 * @author  KavMors(kavmors@163.com)
 *
 * integer/boolean create(string)
 * boolean update(string, string)
 * boolean delete(string)
 * array getAll()
 * integer queryUser(string)
 * boolean moveUser(string/array, string)
 */

namespace wlight\user;
use wlight\basic\AccessToken;
use wlight\util\HttpClient;
use wlight\runtime\ApiException;

include_once (DIR_ROOT.'/wlight/library/api/basic/AccessToken.class.php');
include_once (DIR_ROOT.'/wlight/library/util/HttpClient.class.php');
include_once (DIR_ROOT.'/wlight/library/runtime/ApiException.class.php');

class Groups {
  private $url = 'https://api.weixin.qq.com/cgi-bin/groups';
  private $accessToken;

  /**
   * @throws ApiException
   */
  public function __construct() {
    $accessToken = new AccessToken();
    $this->accessToken = $accessToken->get();
  }

  /**
   * 创建分组
   * @param string $name 分组名
   * @return integer/boolean 分组id(失败时返回false)
   * @throws ApiException
   */
  public function create($name) {
    $json = array(
      'group' => array('name' => urlencode($name))
    );
    $httpClient = new HttpClient($this->url.'/create?access_token='.$this->accessToken);
    $httpClient->setBody(urldecode(json_encode($json)));
    $httpClient->post();
    $result = $httpClient->jsonToArray();

    if (isset($result['group']['id'])) {
      return $result['group']['id'];
    } else {
      throw ApiException::throws(ApiException::ERROR_JSON_ERROR_CODE, 'response: '.$httpClient->getResponse());
    }
    return false;
  }


  /**
   * 修改分组名
   * @param string $groupId 分组id
   * @param string $name 分组名
   * @return boolean true表示成功
   * @throws ApiException
   */
  public function update($groupId, $name) {
    $json = array(
      'group'=> array(
        'id' => intval($groupId),
        'name' => urlencode($name)
      )
    );
    $httpClient = new HttpClient($this->url.'/update?access_token='.$this->accessToken);
    $httpClient->setBody(urldecode(json_encode($json)));
    $httpClient->post();
    $result = $httpClient->jsonToArray();

    if (isset($result['errcode']) && $result['errcode']==0) {  //errcode!=0已在checkErrcode检验
      return true;
    } else {
      throw ApiException::throws(ApiException::ERROR_JSON_ERROR_CODE, 'response: '.$httpClient->getResponse());
    }
    return false;
  }

  /**
   * 删除分组
   * @param string $groupId 分组id
   * @return boolean true表示成功
   * @throws ApiException
   */
  public function delete($groupId) {
    $json = array(
      'group'=> array(
        'id' => intval($groupId)
      )
    );
    $httpClient = new HttpClient($this->url.'/delete?access_token='.$this->accessToken);
    $httpClient->setBody(json_encode($json));
    $httpClient->post();
    $result = $httpClient->jsonToArray();

    if (isset($result['errcode']) && $result['errcode']==0) {  //errcode!=0已在checkErrcode检验
      return true;
    } else {
      throw ApiException::throws(ApiException::ERROR_JSON_ERROR_CODE, 'response: '.$httpClient->getResponse());
    }
    return false;
  }

  /**
   * 查询所有分组
   * @return array 分组数组(失败时返回false)
   * @throws ApiException
   */
  public function getAll() {
    $httpClient = new HttpClient($this->url.'/get?access_token='.$this->accessToken);
    $httpClient->get();
    $result = $httpClient->jsonToArray();

    if (isset($result['groups'])) {
      return $result['groups'];
    } else {
      throw ApiException::throws(ApiException::ERROR_JSON_ERROR_CODE, 'response: '.$httpClient->getResponse());
    }
    return false;
  }

  /**
   * 查询用户所在的分组
   * @param string $openId 用户openid
   * @return integer 分组id(失败时返回false)
   * @throws ApiException
   */
  public function queryUser($openId) {
    $json = array(
      'openid' => $openId
    );
    $httpClient = new HttpClient($this->url.'/getid?access_token='.$this->accessToken);
    $httpClient->setBody(json_encode($json));
    $httpClient->post();
    $result = $httpClient->jsonToArray();

    if (isset($result['groupid'])) {
      return $result['groupid'];
    } else {
      throw ApiException::throws(ApiException::ERROR_JSON_ERROR_CODE, 'response: '.$httpClient->getResponse());
    }
    return false;
  }

  /**
   * 移动用户分组
   * @param string $openidList 用户openid
   * @param string $toGroupId 目标分组id
   * @return boolean true表示成功
   * @throws ApiException
   */
  public function moveUser($openidList, $toGroupId) {
    $json = array(
      'openid'=> $openidList,
      'to_groupid' => intval($toGroupId)
    );
    $httpClient = new HttpClient($this->url.'/members/update?access_token='.$this->accessToken);
    $httpClient->setBody(json_encode($json));
    $httpClient->post();
    $result = $httpClient->jsonToArray();

    if (isset($result['errcode']) && $result['errcode']==0) {  //errcode!=0已在checkErrcode检验
      return true;
    } else {
      throw ApiException::throws(ApiException::ERROR_JSON_ERROR_CODE, 'response: '.$httpClient->getResponse());
    }
    return false;
  }
}
?>