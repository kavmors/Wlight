<?php
/**
 * 客服帐号管理实现类
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\customservice;
use wlight\basic\AccessToken;
use wlight\util\HttpClient;
use wlight\runtime\ApiException;

class Account {
  private $url = 'https://api.weixin.qq.com/customservice/kfaccount';
  private $accessToken;
  private $postfix;

  /**
   * @throws ApiException
   */
	public function __construct() {
    include_once (DIR_ROOT.'/wlight/library/api/basic/AccessToken.class.php');
    include_once (DIR_ROOT.'/wlight/library/util/HttpClient.class.php');
    include_once (DIR_ROOT.'/wlight/library/runtime/ApiException.class.php');

    $accessToken = new AccessToken();
    $this->accessToken = $accessToken->get();
    $this->postfix = WECHAT_ID;
	}

  /**
   * 添加客服帐号
   * @param string $account - 客服帐号(可忽略后缀)
   * @param string $nickname - 昵称
   * @param string $password - 登录密码(未加密)
   * @return boolean - true表示成功
   * @throws ApiException
   */
  public function add($account, $nickname, $password) {
    $json = array(
      'kf_account' => $this->addPostfix($account),
      'nickname' =>$nickname,
      'password' => md5($password)
    );
    $url = $this->url.'/add?access_token='.$this->accessToken;
    $httpClient = new HttpClient($url);
    $httpClient->setBody(json_encode($json));
    $httpClient->post();
    return $this->checkErrcode($httpClient);
  }

  /**
   * 修改客服帐号
   * @param string $account - 客服帐号(可忽略后缀)
   * @param string $nickname - 昵称
   * @param string $password - 登录密码(未加密)
   * @return boolean - true表示成功
   * @throws ApiException
   */
  public function update($account, $nickname, $password) {
    $json = array(
      'kf_account' => $this->addPostfix($account),
      'nickname' => $nickname,
      'password' => md5($password)
    );
    $url = $this->url.'/update?access_token='.$this->accessToken;
    $httpClient = new HttpClient($url);
    $httpClient->setBody(json_encode($json));
    $httpClient->post();
    return $this->checkErrcode($httpClient);
  }

  /**
   * 设置客服帐号头像
   * @param string $account - 客服帐号(可忽略后缀)
   * @param string $img - 头像图片文件
   * @return boolean - true表示成功
   * @throws ApiException
   */
  public function uploadHeadimg($account, $img) {
    $url = $this->url.'/uploadheadimg?access_token='.$this->accessToken.'&kf_account='.$this->addPostfix($account);
    $httpClient = new HttpClient($url);
    $httpClient->upload(array('media'=>$img));
    return $this->checkErrcode($httpClient);
  }

  /**
   * 删除客服帐号
   * @param string $account - 客服帐号(可忽略后缀)
   * @param string $nickname - 昵称
   * @return boolean - true表示成功
   * @throws ApiException
   */
  public function delete($account, $nickname) {
    $url = $this->url.'/del?access_token='.$this->accessToken.'&kf_account='.$this->addPostfix($account);
    $httpClient = new HttpClient($url);
    $httpClient->get();
    return $this->checkErrcode($httpClient);
  }

  /**
   * 获取所有客服帐号
   * @return array - 客服帐号数组(失败时返回false)
   * @throws ApiException
   */
  public function getAll() {
    $url = 'https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token='.$this->accessToken;
    $httpClient = new HttpClient($url);
    $httpClient->get();
    $result = $httpClient->jsonToArray();

    if (isset($result['kf_list'])) {
      return $result['kf_list'];
    } else {
      throw ApiException::illegalJsonException('response: '.$httpClient->getResponse());
    }

    //never
    return false;
  }

  /**
   * 获取在线客服接待信息
   * @return array - 客服接待信息集合(失败时返回false)
   * @throws ApiException
   */
  public function getOnlineList() {
    $url = 'https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist?access_token='.$this->accessToken;
    $httpClient = new HttpClient($url);
    $httpClient->get();
    $result = $httpClient->jsonToArray();

    if (isset($result['kf_online_list'])) {
      return $result['kf_online_list'];
    } else {
      throw ApiException::illegalJsonException('response: '.$httpClient->getResponse());
    }

    //never
    return false;
  }

  //检查返回的全局码
  private function checkErrcode($httpClient) {
    $result = $httpClient->jsonToArray();

    if (isset($result['errcode'])) {
      return true;
    } else {
      throw ApiException::illegalJsonException('response: '.$httpClient->getResponse());
    }

    //never
    return false;
  }

  //为未添加后缀的客服帐号添加后缀
  private function addPostfix($account) {
    $at = stripos($account, '@');
    if ($at===false) {
      return $account.'@'.$this->postfix;
    } else {
      return $account;
    }
  }
}
?>