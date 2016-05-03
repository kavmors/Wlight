<?php
/**
 * Http请求辅助类
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\util;
use wlight\runtime\ApiException;

class HttpClient {
  const COMMON_USERAGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537.36';
  private $url;
  private $method = 'GET';
  private $reqHeader;
  private $body;

  private $respHeader;
  private $response;
  private $status;

  private $proxy;
  private $autoRedirect;

  //编码转换方法(static)
  /**
   * gb2312转换为utf8
   * @param string $word - 要转换的字符串
   * @return string - 转换后的字符串
   */
  public static function escapeToUtf8($word) {
    return mb_convert_encoding($word, 'utf-8');
  }

  /**
   * gb2312转换为utf8
   * @param string $word - 要转换的字符串
   * @return string - 转换后的字符串
   */
  public static function escapeToGb2312($word) {
    return mb_convert_encoding($word, 'gb2312');
  }

  public function __construct($url = '') {
    include_once (DIR_ROOT.'/wlight/library/runtime/ApiException.class.php');
    $this->url = $url;
  }

  /**
   * 设置请求url
   * @param string $url - 请求url
   */
  public function setUrl($url) {
    $this->url = $url;
  }

  /**
   * 设置请求方式
   * @param string $method - GET或POST
   */
  public function setMethod($method) {
    $this->method = $method;
  }

  /**
   * 设置请求头部
   * @param array $header - 数组形式的请求头部,以key-value键值对存在
   */
  public function setHeader($header) {
    $this->reqHeader = $this->handleHeaderArray($header);
  }

  /**
   * 设置请求正文部分
   * @param array/string $body - 正文内容
   *        数组形式设置key-value对或字符串形式设置流数据
   * @param boolean $urlencode - 当$body为array时,true表示需要对每个value进行Urlencode
   */
  public function setBody($body, $urlencode=true) {
    if (is_array($body)) {
      if ($urlencode) {
        $this->body = http_build_query($body);
      } else {
        $this->body = $this->buildQueryWithoutEncode($body);
      }
    } elseif (is_string($body)) {
      $this->body = $body;
    }
  }

  /**
   * 执行请求
   * @param integer $timeout - 可选,请求超时时间(默认5s)
   * @return string - 响应正文
   */
  public function exec($timeout = 5) {
    $curl = curl_init();

    if (is_array($this->reqHeader)) {
      curl_setopt($curl, CURLOPT_HTTPHEADER, $this->reqHeader);
    }
    if (!empty($this->proxy)) {
      curl_setopt($curl, CURLOPT_PROXY, $this->proxy);
    }
    if ($this->autoRedirect==true) {
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    }

    //设置url
    if (strtoupper($this->method)=='GET' &&
        !empty($this->body) &&
        is_string($this->body)) {
        curl_setopt($curl, CURLOPT_URL, $this->url.'?'.$this->body);
    } else {
      curl_setopt($curl, CURLOPT_URL, $this->url);
    }
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_USERAGENT, self::COMMON_USERAGENT);
    if ($this->method=='POST') {
      curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $this->body);
    }

    $this->handleResponse($curl);
    curl_close($curl);
    return $this->response;
  }

  /**
   * 执行get请求
   * @param integer $timeout - 可选,请求超时时间(默认5s)
   * @return string - 响应正文
   */
  public function get($timeout = 5) {
    $this->method = 'GET';
    return $this->exec($timeout);
  }

  /**
   * 执行post请求
   * @param integer $timeout - 可选,请求超时时间(默认5s)
   * @return string - 响应正文
   */
  public function post($timeout = 5) {
    $this->method = 'POST';
    return $this->exec($timeout);
  }

  /**
   * 上传文件
   * @param array $files - 文件参数,形式为(name=>path)的数组
   * @param array $extraParam - 额外参数,形式为(key=>value)的数组
   * @param integer $timeout - 可选,请求超时时间(默认30s)
   * @return string - 请求响应,失败则返回false
   */
  public function upload($files, $extraParam=null, $timeout = 30) {
    if (!is_array($files)) {
      return false;
    }
    $this->method = 'POST';
    //针对5.5以上版本使用CURLFile
    if (class_exists("\CURLFile")) {
      foreach ($files as $name => $path) {
        $files[$name] = new \CURLFile($path);
      }
    } else {
      foreach ($files as $name => $path) {
        $files[$name] = '@'.$path;
      }
    }

    if (is_array($extraParam) && count($extraParam)!=0) {
      $files = array_merge($extraParam, $files);
    }
    $this->body = $files;
    return $this->exec($timeout);
  }

  /**
   * 下载文件(默认GET方式下载,POST需要先设置setMethod及setBody)
   * @param string $file - 文件路径
   * @param string $timeout - 下载超时时间,默认30s
   * @return integer - 文件大小
   */
  public function download($file, $timeout=30) {
    file_put_contents($file, exec($timeout));
    return filesize($file);
  }

  /**
   * 将当前响应按JSON解析为数组
   * @return array - 对象对组
   * @throws ApiException
   */
  public function jsonToArray() {
    if ($this->getStatus()!=200 || $this->getResponse()=='') {
      throw ApiException::httpException('status code: '.$this->getStatus());
      return false;
    }

    //解析json结构
    $stream = json_decode($this->getResponse(), true);
    if (!$stream) {
      throw ApiException::jsonDecodeException('response: '.$this->getResponse());
      return false;
    }

    //检查errcode
    if (isset($stream['errcode']) && intval($stream['errcode'])!=0) {
      throw new ApiException($stream['errmsg'], $stream['errcode']);
      return false;
    }
    return $stream;
  }

  //以下是Getter
  public function getHeader() {
    return $this->respHeader;
  }

  public function getStatus() {
    return $this->status;
  }

  public function getResponse() {
    return $this->response;
  }

  public function getMethod() {
    return $this->method;
  }

  //连接参数,不进行urlencode
  private function buildQueryWithoutEncode($param) {
    //上级调用保证is_array($param)
    $queryStr = '';
    foreach ($param as $key => $value) {
      $queryStr .= "$key=$value&";
    }
    return substr($queryStr, 0, -1);
  }

  //解析头部数组,将key-value对改成array("$key: $value")形式
  private function handleHeaderArray($header) {
    $result = array();
    foreach ($header as $key => $value) {
      $result[] = "$key: $value";
    }
    return $result;
  }

  //执行curl并处理结果
  private function handleResponse($curl) {
    $response = curl_exec($curl);
    $bodyStart = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $this->respHeader = $this->explodeHeader(trim(substr($response, 0, $bodyStart)));
    $this->response = trim(substr($response, $bodyStart));
    $this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  }

  //以换行分割头部信息
  private function explodeHeader($str) {
    $header = array();
    $str = nl2br($str);
    $exploder = explode('<br />', $str);
    foreach ($exploder as $row) {
      $colon = stripos($row, ':');
      if (!$colon) {
        continue;
      }
      $key = trim(substr($row, 0, $colon));
      $value = trim(substr($row, $colon+1));
      $header[$key][] = $value;
    }
    return $header;
  }
}
?>