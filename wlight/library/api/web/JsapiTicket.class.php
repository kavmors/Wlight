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
use wlight\core\support\Recorder;

class JsapiTicket {
  private $appid;
  private $accessToken;
  private $file;
  private $runtimeRoot;
  private $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';
  private $locker;

  /**
   * @throws ApiException
   */
  public function __construct() {
    include_once (DIR_ROOT.'/wlight/library/api/basic/AccessToken.class.php');
    include_once (DIR_ROOT.'/wlight/library/util/HttpClient.class.php');
    include_once (DIR_ROOT.'/wlight/library/runtime/ApiException.class.php');
    include_once (DIR_ROOT.'/wlight/library/core/support/Locker.class.php');
    include_once (DIR_ROOT.'/wlight/library/core/support/Recorder.class.php');

    $this->appid = APP_ID;
    $this->runtimeRoot = RUNTIME_ROOT;
    $this->file = $this->loadTicketRecord();
    $accessToken = new AccessToken();
    $this->accessToken = $accessToken->get();
    $this->locker = new Locker(LOCK_JSAPI_TICKET);
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
      $reader = new Recorder($this->file);
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
    $locker->lock();

    $url = $this->url."/?access_token=$this->accessToken&type=jsapi";
    $httpClient = new HttpClient($url);
    $httpClient->get();
    if ($httpClient->getStatus()!=200 || empty($httpClient->getResponse())) {
      $locker->unlock();
      throw ApiException::httpException('status code: '.$httpClient->getStatus());
      return false;
    }

    //解析json结构
    $stream = json_decode($httpClient->getResponse(), true);
    if (!$stream) {
      $locker->unlock();
      throw ApiException::jsonDecodeException('response: '.$httpClient->getResponse());
      return false;
    }

    if (isset($stream['ticket'])) {
      //提取参数
      $jsapi_ticket = $stream['ticket'];
      $expires_in = $stream['expires_in'];
      $expires_time = intval(time())+intval($expires_in)-60;    //60s超时缓冲
      $file_stream = json_encode(array('expires_time'=>$expires_time, 'jsapi_ticket'=>$jsapi_ticket));

      $writer = new Recorder($this->file);
      $writer->write($file_stream);

      $locker->unlock();

      return $jsapi_ticket;
    } else {
      $locker->unlock();

      if (isset($stream['errcode'])) {
        throw new ApiException($stream['errmsg'], $stream['errcode']);
      } else {
        throw ApiException::illegalJsonException('response: '.$httpClient->getResponse());
      }
    }
    $locker->unlock();
    return false;
  }

  //获取当前appid下的Ticket记录文件
  private function loadTicketRecord() {
    return $this->runtimeRoot.'/cache/'.$this->appid.'_jsapi_ticket.json.php';
  }
}
?>