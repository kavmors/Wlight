<?php
/**
 * Access Token实现类
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\basic;
use wlight\util\HttpClient;
use wlight\runtime\ApiException;

class AccessToken {
  private $appid;
  private $appsecret;
  private $runtimeRoot;
	private $file;
	private $url = 'https://api.weixin.qq.com/cgi-bin/token';

	public function __construct() {
    include_once (self::getDirRoot().'/wlight/library/util/HttpClient.class.php');
    include_once (self::getDirRoot().'/wlight/library/runtime/ApiException.class.php');

		$this->appid = self::getAppId();
		$this->appsecret = self::getAppSecret();
    $this->runtimeRoot = self::getRuntimeRoot();
    $this->file = $this->loadTokenRecord();
    $this->url .= "?grant_type=client_credential&appid=$this->appid&secret=$this->appsecret";
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
			$record = json_decode(self::getJsonStr($this->file), true);
      
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
    $httpClient = new HttpClient($this->url);
    $httpClient->get();
    if ($httpClient->getStatus()!=200 || empty($httpClient->getResponse())) {
      throw ApiException::httpException('status code: '.$httpClient->getStatus());
      return false;
    }

    //解析json结构
		$stream = json_decode($httpClient->getResponse(), true);
    if (!$stream) {
      throw ApiException::jsonDecodeException('response: '.$httpClient->getResponse());
      return false;
    }

    if (isset($stream['access_token'])) {
      //提取参数
  		$access_token = $stream['access_token'];
  		$expires_in = $stream['expires_in'];
  		$expires_time = intval(time())+intval($expires_in)-60;    //60s超时缓冲
  		$file_stream = json_encode(array('expires_time'=>$expires_time, 'access_token'=>$access_token));
      $file_stream = '<?php exit; ?>'.$file_stream;
  		file_put_contents($this->file, $file_stream);
      chmod($this->file, 0777);

  		return $access_token;
    } else {
      if (isset($data['errcode'])) {
        throw new ApiException($data['errmsg'], $data['errcode']);
      } else {
        throw ApiException::illegalJsonException('response: '.$httpClient->getResponse());
      }
    }
    return false;
	}

  //解析出json格式的字符串
  private function getJsonStr($file) {
    $str = file_get_contents($file);
    $start = stripos($str, '?>') + 2;
    return substr($str, $start);
  }

  //以下方法供外置应用调用本类时读取相关配置所用
  
  //获取当前appid下的Token记录文件
  private function loadTokenRecord() {
    return $this->runtimeRoot.'/cache/'.$this->appid.'_access_token.json.php';
  }

  //获取项目根目录
  private static function getDirRoot() {
    return defined('DIR_ROOT')? DIR_ROOT: \wlight\dev\Config::get('DIR_ROOT');
  }

  //获取APPID配置
  private static function getAppId() {
    return defined('APP_ID')? APP_ID: \wlight\dev\Config::get('APP_ID');
  }

  //获取APPSECRET配置
  private static function getAppSecret() {
    return defined('APP_SECRET')? APP_SECRET: \wlight\dev\Config::get('APP_SECRET');
  }

  //获取RUNTIME_ROOT配置
  private static function getRuntimeRoot() {
    return defined('RUNTIME_ROOT')? RUNTIME_ROOT: \wlight\dev\Config::get('RUNTIME_ROOT');
  }
}
?>