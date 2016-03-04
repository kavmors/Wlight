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
    include_once (self::getDirRoot().'/wlight/library/api/basic/AccessToken.class.php');
    include_once (self::getDirRoot().'/wlight/library/util/HttpClient.class.php');
    include_once (self::getDirRoot().'/wlight/library/runtime/ApiException.class.php');

    $this->appid = self::getAppId();
    $this->runtimeRoot = self::getRuntimeRoot();
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
      $record = json_decode(self::getJsonStr($this->file), true);

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
    $this->lock();

    $url = $this->url."/?access_token=$this->accessToken&type=jsapi";
    $httpClient = new HttpClient($url);
    $httpClient->get();
    if ($httpClient->getStatus()!=200 || empty($httpClient->getResponse())) {
      $this->unlock();
      throw ApiException::httpException('status code: '.$httpClient->getStatus());
      return false;
    }

    //解析json结构
    $stream = json_decode($httpClient->getResponse(), true);
    if (!$stream) {
      $this->unlock();
      throw ApiException::jsonDecodeException('response: '.$httpClient->getResponse());
      return false;
    }

    if (isset($stream['ticket'])) {
      //提取参数
      $jsapi_ticket = $stream['ticket'];
      $expires_in = $stream['expires_in'];
      $expires_time = intval(time())+intval($expires_in)-60;    //60s超时缓冲
      $file_stream = json_encode(array('expires_time'=>$expires_time, 'jsapi_ticket'=>$jsapi_ticket));
      $file_stream = '<?php exit; ?>'.$file_stream;
      file_put_contents($this->file, $file_stream);
      chmod($this->file, 0777);

      $this->unlock();

      return $jsapi_ticket;
    } else {
      $this->unlock();
      
      if (isset($stream['errcode'])) {
        throw new ApiException($stream['errmsg'], $stream['errcode']);
      } else {
        throw ApiException::illegalJsonException('response: '.$httpClient->getResponse());
      }
    }
    $this->unlock();
    return false;
  }

  //解析出json格式的字符串
  private function getJsonStr($file) {
    $str = file_get_contents($file);
    $start = stripos($str, '?>') + 2;
    return substr($str, $start);
  }

  //文件锁
  private $locker;

  private function lock() {
    $this->locker = fopen(self::getLockJspiTicket(), 'r');
    flock($this->locker, LOCK_EX);
  }

  private function unlock() {
    flock($this->locker, LOCK_UN);
    fclose($this->locker);
  }

  //以下方法供外置应用调用本类时读取相关配置所用
  
  //获取当前appid下的Ticket记录文件
  private function loadTicketRecord() {
    return $this->runtimeRoot.'/cache/'.$this->appid.'_jsapi_ticket.json.php';
  }

  //获取项目根目录
  private static function getDirRoot() {
    return defined('DIR_ROOT')? DIR_ROOT: \wlight\dev\Config::get('DIR_ROOT');
  }

  //获取AppId
  private static function getAppId() {
    return defined('APP_ID')? APP_ID: \wlight\dev\Config::get('APP_ID');
  }

  //获取RUNTIME_ROOT配置
  private static function getRuntimeRoot() {
    return defined('RUNTIME_ROOT')? RUNTIME_ROOT: \wlight\dev\Config::get('RUNTIME_ROOT');
  }

  //获取LOCK_JSAPI_TICKET配置
  private static function getLockJspiTicket() {
    return defined('LOCK_JSAPI_TICKET')? LOCK_JSAPI_TICKET: \wlight\dev\Config::get('LOCK_JSAPI_TICKET');
  }
}
?>