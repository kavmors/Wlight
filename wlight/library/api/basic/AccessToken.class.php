<?php
/**
 * Access Token控制类
 * http://mp.weixin.qq.com/wiki/11/0e4b294685f817b95cbed85ba5e82b8f.html
 * @author  KavMors(kavmors@163.com)
 *
 * string get(boolean)
 */

namespace wlight\basic;
use wlight\util\HttpClient;
use wlight\runtime\ApiException;
use wlight\runtime\Log;

include_once (DIR_ROOT.'/wlight/library/util/HttpClient.class.php');
include_once (DIR_ROOT.'/wlight/library/runtime/ApiException.class.php');
include_once (DIR_ROOT.'/wlight/library/runtime/Log.class.php');

class AccessToken {
  private $appid;
  private $appsecret;
  private $runtimeRoot;
  private $file;
  private $url = 'https://api.weixin.qq.com/cgi-bin/token';

  public function __construct() {
    $this->appid = APP_ID;
    $this->appsecret = APP_SECRET;
    $this->runtimeRoot = RUNTIME_ROOT;
    $this->file = $this->loadTokenRecord();
  }

  /**
   * 获取Access Token(或刷新Token值)
   * @param boolean $reload true表示重新获取最新Token值
   * @return string token字符串(请求失败返回false)
   * @throws ApiException
   */
  public function get($reload = false) {
    if ($reload) {
      return $this->reloadToken();
    }
    if (file_exists($this->file)) {
      $content = file_get_contents($this->file);
      $content = trim(strstr($content, '{'));
      $record = json_decode($content, true);

      if (!$record) {   //json结构检验
        return $this->reloadToken();
      } elseif ($record['expires_time'] <= time()) {    //token超时检验
        return $this->reloadToken();
      } else {
        return $record['access_token'];
      }
    } else {
      return $this->reloadToken();
    }
  }

  //刷新token值
  private function reloadToken() {
    $fp = fopen($this->file, "w");
    if (flock($fp, LOCK_EX)) {
      Log::i('Lock', 'AccessToken');
      try {
        $url = $this->url."?grant_type=client_credential&appid=$this->appid&secret=$this->appsecret";
        $httpClient = new HttpClient($url);
        $httpClient->get();
        $stream = $httpClient->jsonToArray();

        if (isset($stream['access_token'])) {
          //提取参数
          $access_token = $stream['access_token'];
          $expires_in = $stream['expires_in'];
          $expires_time = intval(time())+intval($expires_in)-60;    //60s超时缓冲
          $file_stream = json_encode(array('expires_time'=>$expires_time, 'access_token'=>$access_token));

          fwrite($fp, "<?php exit; ?>\n");
          fwrite($fp, $file_stream);

          Log::i('Unlock', 'AccessToken');
          flock($fp, LOCK_UN);
          fclose($fp);
          @chmod($this->file, DEFAULT_PERMISSION);
          return $access_token;
        } else {
          throw ApiException::throws(ApiException::ERROR_JSON_ERROR_CODE, 'response: '.$httpClient->getResponse());
        }
      } catch (ApiException $e) {
        Log::i('Unlock', 'AccessToken');
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

  //获取当前appid下的Token记录文件
  private function loadTokenRecord() {
    return $this->runtimeRoot.'/cache/access_token.json.php';
  }
}
?>