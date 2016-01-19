<?php
/**
 * 用户分组管理
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\user;
use wlight\basic\AccessToken;
use wlight\util\HttpClient;
use wlight\runtime\ApiException;

class Groups {
	private $url = 'https://api.weixin.qq.com/cgi-bin/groups';
  private $accessToken;

  /**
   * @throws ApiException
   */
	public function __construct() {
    include_once (self::getDirRoot().'/wlight/library/api/basic/AccessToken.class.php');
    include_once (self::getDirRoot().'/wlight/library/util/HttpClient.class.php');
    include_once (self::getDirRoot().'/wlight/library/runtime/ApiException.class.php');

    $accessToken = new AccessToken();
    $this->accessToken = $accessToken->get();
	}

  /**
   * 创建分组
   * @param string $name - 分组名
   * @return integer - 分组id(失败时返回false)
   * @throws ApiException
   */
  public function create($name) {
    $json = array(
      'group' => array('name' => $name)
    );
    $httpClient = new HttpClient($this->url.'/create?access_token='.$this->accessToken);
    $httpClient->setBody(json_encode($json));
    $httpClient->post();
    $result = $this->checkErrcode($httpClient);
    if ($result) {
      if (isset($result['group']['id'])) {
        return $result['group']['id'];
      } else {
        throw ApiException::illegalJsonException('response: '.$httpClient->getResponse());
      }
    }
    return false;
  }

  /**
   * 查询所有分组
   * @return array - 分组数组(失败时返回false)
   * @throws ApiException
   */
  public function getAll() {
    $httpClient = new HttpClient($this->url.'/get?access_token='.$this->accessToken);
    $httpClient->get();
    $result = $this->checkErrcode($httpClient);
    if ($result) {
      if (isset($result['groups'])) {
        return $result['groups'];
      } else {
        throw ApiException::illegalJsonException('response: '.$httpClient->getResponse());
      }
    }
    return false;
  }

  /**
   * 查询用户所在的分组
   * @param string $openId - 用户openid
   * @return integer - 分组id(失败时返回false)
   * @throws ApiException
   */
  public function queryUser($openId) {
    $json = array(
      'openid' => $openId
    );
    $httpClient = new HttpClient($this->url.'/getid?access_token='.$this->accessToken);
    $httpClient->setBody(json_encode($json));
    $httpClient->post();
    $result = $this->checkErrcode($httpClient);
    if ($result) {
      if (isset($result['groupid'])) {
        return $result['groupid'];
      } else {
        throw ApiException::illegalJsonException('response: '.$httpClient->getResponse());
      }
    }
    return false;
  }

  /**
   * 修改分组名
   * @param string $groupId - 分组id
   * @param string $name - 分组名
   * @return boolean - true表示成功
   * @throws ApiException
   */
  public function update($groupId, $name) {
    $json = array(
      'group '=> array(
        'id' => $groupId,
        'name' => $name
      )
    );
    $httpClient = new HttpClient($this->url.'/update?access_token='.$this->accessToken);
    $httpClient->setBody(json_encode($json));
    $httpClient->post();
    $result = $this->checkErrcode($httpClient);
    if ($result) {
      if (isset($result['errcode'])) {  //errcode!=0已在checkErrcode检验
        return true;
      } else {
        throw ApiException::illegalJsonException('response: '.$httpClient->getResponse());
      }
    }
    return false;
  }

  /**
   * 移动用户分组
   * @param string/array $openidList - 用户openid的列表(不超过50)
   * @param string $toGroupId - 目标分组id
   * @return boolean - true表示成功
   * @throws ApiException
   */
  public function moveUpser($openidList, $toGroupId) {
    if (is_string($openidList)) {
      $openidList = array($openidList);
    }
    $json = array(
      'openid_list '=> $openidList,
      'to_groupid' => $toGroupId
    );
    $httpClient = new HttpClient($this->url.'/members/batchupdate?access_token='.$this->accessToken);
    $httpClient->setBody(json_encode($json));
    $httpClient->post();
    $result = $this->checkErrcode($httpClient);
    if ($result) {
      if (isset($result['errcode'])) {  //errcode!=0已在checkErrcode检验
        return true;
      } else {
        throw ApiException::illegalJsonException('response: '.$httpClient->getResponse());
      }
    }
    return false;
  }

  /**
   * 删除分组
   * @param string $groupId - 分组id
   * @return boolean - true表示成功
   * @throws ApiException
   */
  public function delete($groupId) {
    $json = array(
      'group '=> array(
        'id' => $groupId
      )
    );
    $httpClient = new HttpClient($this->url.'/delete?access_token='.$this->accessToken);
    $httpClient->setBody(json_encode($json));
    $httpClient->post();
    $result = $this->checkErrcode($httpClient);
    if ($result) {
      if (isset($result['errcode'])) {  //errcode!=0已在checkErrcode检验
        return true;
      } else {
        throw ApiException::illegalJsonException('response: '.$httpClient->getResponse());
      }
    }
    return false;
  }

  //检查返回状态码
  private function checkErrcode($httpClient) {
    if ($httpClient->getStatus()!=200 || empty($httpClient->getResponse())) {
      throw ApiException::httpException('status code: '.$httpClient->getStatus());
      return false;
    }
    $result = json_decode($httpClient->getResponse(), true);
    if (!$result) {
      throw ApiException::jsonDecodeException('response: '.$httpClient->getResponse());
      return false;
    }
    if (isset($result['errcode']) && $result['errcode']!=0) {
      throw new ApiException($result['errmsg'], $result['errcode']);  //非0状态码
      return false;
    }
    return $result;
  }

  //以下方法供外置应用调用本类时读取相关配置所用
  
  //获取项目根目录
  private static function getDirRoot() {
    return defined('DIR_ROOT')? DIR_ROOT: \wlight\dev\Config::get('DIR_ROOT');
  }
}
?>