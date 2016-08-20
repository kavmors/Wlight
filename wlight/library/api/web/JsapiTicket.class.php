<?php
/**
 * 获取JsapiTicket(调用js接口凭证)
 * http://mp.weixin.qq.com/wiki/11/74ad127cc054f6b80759c40f77ec03db.html#.E9.99.84.E5.BD.951-JS-SDK.E4.BD.BF.E7.94.A8.E6.9D.83.E9.99.90.E7.AD.BE.E5.90.8D.E7.AE.97.E6.B3.95
 * @author  KavMors(kavmors@163.com)
 *
 * string get(boolean)
 */

namespace wlight\web;
use wlight\basic\AccessToken;
use wlight\util\HttpClient;
use wlight\runtime\ApiException;
use wlight\runtime\Log;

include_once (DIR_ROOT.'/wlight/library/api/basic/AccessToken.class.php');
include_once (DIR_ROOT.'/wlight/library/util/HttpClient.class.php');
include_once (DIR_ROOT.'/wlight/library/runtime/ApiException.class.php');
include_once (DIR_ROOT.'/wlight/library/runtime/Log.class.php');

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
    $this->appid = APP_ID;
    $this->runtimeRoot = RUNTIME_ROOT;
    $this->file = $this->loadTicketRecord();
    $accessToken = new AccessToken();
    $this->accessToken = $accessToken->get();
  }

  /**
   * 获取Jsapi Ticket(或刷新Ticket值)
   * @param boolean $reload true表示重新获取最新Ticket值
   * @return string token字符串(请求失败返回false)
   * @throws ApiException
   */
  public function get($reload = false) {
    if ($reload) {
      return $this->reloadTicket();
    }
    if (file_exists($this->file)) {
      $content = file_get_contents($this->file);
      $content = trim(strstr($content, '{'));
      $record = json_decode($content, true);

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
    $fp = fopen($this->file, "w");
    if (flock($fp, LOCK_EX)) {
      Log::i('Lock', 'JsapiTicket');
      try {
      	$url = $this->url."?access_token=$this->accessToken&type=wx_card";
        $httpClient = new HttpClient($url);
        $httpClient->get();
        $stream = $httpClient->jsonToArray();

        if (isset($stream['ticket'])) {
          //提取参数
          $jsapi_ticket = $stream['ticket'];
          $expires_in = $stream['expires_in'];
          $expires_time = intval(time())+intval($expires_in)-60;    //60s超时缓冲
          $file_stream = json_encode(array('expires_time'=>$expires_time, 'jsapi_ticket'=>$jsapi_ticket));

          fwrite($fp, "<?php exit; ?>\n");
          fwrite($fp, $file_stream);

          Log::i('Unlock', 'JsapiTicket');
          flock($fp, LOCK_UN);
          fclose($fp);
          @chmod($this->file, DEFAULT_PERMISSION);
          return $jsapi_ticket;
        } else {
          throw ApiException::throws(ApiException::ERROR_JSON_ERROR_CODE, 'response: '.$httpClient->getResponse());
        }
      } catch (ApiException $e) {
        Log::i('Unlock', 'JsapiTicket');
        flock($fp, LOCK_UN);
        fclose($fp);
        throw $e;
        return false;
      }
    } else {
      fclose($fp);
      throw ApiException::throws(ApiException::FILE_LOCK_ERROR_CODE);
      return false;
    }
  }

  //获取当前appid下的Ticket记录文件
  private function loadTicketRecord() {
    return $this->runtimeRoot.'/cache/jsapi_ticket.json.php';
  }
}
?>