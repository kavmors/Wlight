<?php
/**
 * 获取JsapiTicket(调用js接口凭证)
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\web;
use wlight\basic\AccessToken;
use wlight\util\HttpClient;
use wlight\runtime\ApiException;
use wlight\core\support\Locker;
use wlight\core\support\RecordManager;

class JsapiTicket {
  private $appid;
  private $accessToken;
  private $file;
  private $runtimeRoot;
  private $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';

  /**
   * @throws ApiException
   */
  public function __construct() {
    include_once (DIR_ROOT.'/wlight/library/api/basic/AccessToken.class.php');
    include_once (DIR_ROOT.'/wlight/library/util/HttpClient.class.php');
    include_once (DIR_ROOT.'/wlight/library/runtime/ApiException.class.php');
    include_once (DIR_ROOT.'/wlight/library/core/support/Locker.class.php');
    include_once (DIR_ROOT.'/wlight/library/core/support/RecordManager.class.php');

    $this->appid = APP_ID;
    $this->runtimeRoot = RUNTIME_ROOT;
    $this->file = $this->loadTicketRecord();
    $accessToken = new AccessToken();
    $this->accessToken = $accessToken->get();
  }

  /**
   * 获取Jsapi Ticket(或刷新Ticket值)
   * @param boolean $reload - true表示重新获取最新Ticket值
   * @return string - token字符串(请求失败返回false)
   * @throws ApiException
   */
  public function get($reload = false) {
    if ($reload) {
      return $this->reloadTicket();
    }
    if (file_exists($this->file)) {
      $reader = new RecordManager($this->file);
      $record = json_decode($reader->read(), true);

      if (!$record) {   //json结构检验
        return $this->reloadTicket();
      } elseif ($record['expires_time'] <= time()) {    //ticket超时检验
        return $this->reloadTicket();
      } else {
        return $record['jsapi_ticket'];
      }
    } else {
      return $this->reloadTicket();
    }
  }

  //刷新ticket值
  private function reloadTicket() {
    Locker::getInstance(LOCK_JSAPI_TICKET)->lock();

    $url = $this->url."/?access_token=$this->accessToken&type=jsapi";
    $httpClient = new HttpClient($url);
    $httpClient->get();
    $stream = $httpClient->jsonToArray();

    if (isset($stream['ticket'])) {
      //提取参数
      $jsapi_ticket = $stream['ticket'];
      $expires_in = $stream['expires_in'];
      $expires_time = intval(time())+intval($expires_in)-60;    //60s超时缓冲
      $file_stream = json_encode(array('expires_time'=>$expires_time, 'jsapi_ticket'=>$jsapi_ticket));

      $writer = new RecordManager($this->file);
      $writer->write($file_stream);

      Locker::getInstance(LOCK_JSAPI_TICKET)->unlock();

      return $jsapi_ticket;
    } else {
      throw ApiException::errorJsonException('response: '.$httpClient->getResponse());
    }

    //never
    Locker::getInstance(LOCK_JSAPI_TICKET)->unlock();
    return false;
  }

  //获取当前appid下的Ticket记录文件
  private function loadTicketRecord() {
    return $this->runtimeRoot.'/cache/'.$this->appid.'_jsapi_ticket.json.php';
  }
}
?>