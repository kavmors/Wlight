<?php
/**
 * Access Token实现类
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\basic;
use wlight\util\HttpClient;
use wlight\runtime\ApiException;
use wlight\core\support\Locker;
use wlight\core\support\RecordManager;

class AccessToken {
  private $appid;
  private $appsecret;
  private $runtimeRoot;
	private $file;
	private $url = 'https://api.weixin.qq.com/cgi-bin/token';

	public function __construct() {
    include_once (DIR_ROOT.'/wlight/library/util/HttpClient.class.php');
    include_once (DIR_ROOT.'/wlight/library/runtime/ApiException.class.php');
    include_once (DIR_ROOT.'/wlight/library/core/support/Locker.class.php');
    include_once (DIR_ROOT.'/wlight/library/core/support/RecordManager.class.php');

		$this->appid = APP_ID;
		$this->appsecret = APP_SECRET;
    $this->runtimeRoot = RUNTIME_ROOT;
    $this->file = $this->loadTokenRecord();
	}

	/**
	 * 获取Access Token(或刷新Token值)
	 * @param boolean $reload - true表示重新获取最新Token值
   * @return string - token字符串(请求失败返回false)
   * @throws ApiException
	 */
	public function get($reload = false) {
		if ($reload) {
      return $this->reloadToken();
    }
		if (file_exists($this->file)) {
      $reader = new RecordManager($this->file);
			$record = json_decode($reader->read(), true);

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
    Locker::getInstance(LOCK_ACCESS_TOKEN)->lock();

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

      $writer = new RecordManager($this->file);
      $writer->write($file_stream);

      Locker::getInstance(LOCK_ACCESS_TOKEN)->unlock();

  		return $access_token;
    } else {
      throw ApiException::errorJsonException('response: '.$httpClient->getResponse());
    }

    //never
    Locker::getInstance(LOCK_ACCESS_TOKEN)->unlock();
    return false;
	}

  //获取当前appid下的Token记录文件
  private function loadTokenRecord() {
    return $this->runtimeRoot.'/cache/'.$this->appid.'_access_token.json.php';
  }
}
?>