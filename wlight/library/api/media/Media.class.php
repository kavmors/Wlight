<?php
/**
 * 临时素材管理接口
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\media;
use wlight\basic\AccessToken;
use wlight\util\HttpClient;
use wlight\runtime\ApiException;

class Media {
  private $url = 'https://api.weixin.qq.com/cgi-bin/media';
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
   * 上传一个素材
   * @param string $mediaFile - 完整(绝对路径)文件路径
   * @param string $type - 可选,上传媒体文件的类型(image、voice、video、thumb),不填则根据后缀名判断
   * @return string - mediaId媒体id(请求失败返回false)
   * @throws ApiException
   */
  public function upload($mediaFile, $type=null) {
    if (!file_exists($mediaFile)) {
      throw ApiException::fileNotExistsException("file: $mediaFile");
      return false;
    }
    if ($type==null) {
      $type = $this->parseMediaType($mediaFile);
    }
    $url = $this->url.'/upload?access_token='.$this->accessToken.'&type='.$type;
    $httpClient = new HttpClient($url);
    $httpClient->upload(array('media'=>$mediaFile));
    $result = $this->checkErrcode($httpClient);
    if ($result) {
      if (isset($result['media_id'])) {
        return $result['media_id'];
      } else {
        throw ApiException::illegalJsonException('response: '.$httpClient->getResponse());
      }
    }
    return false;
  }

  /**
   * 下载一个素材
   * @param string $mediaId - 媒体id,通过上传获得
   * @param string $toFile - 可选,下载到文件的绝对路径,不填则默认路径为RES_ROOT/$mediaId
   * @return integer - 下载到的文件大小(失败时返回false)
   * @throws ApiException
   */
  public function download($mediaId, $toFile=null) {
    //使用http协议
   // $this->url = str_replace('https', 'http', $this->url);
    $url = $this->url.'/get?access_token='.$this->accessToken.'&media_id='.$mediaId;
    $httpClient = new HttpClient($url);
    $httpClient->get(30);
    if ($httpClient->getStatus()!=200 || empty($httpClient->getResponse())) {
      throw ApiException::httpException('status code: '.$httpClient->getStatus());
      return false;
    }
    $header = $httpClient->getHeader();
    //Content-Type为text/时表示带有errcode错误信息
    if (!isset($header['Content-Type']) || stripos($header['Content-Type'], 'text')!==false) {
      $result = json_decode($httpClient->getResponse(), true);
      if (!$result) {
        throw ApiException::jsonDecodeException('response: '.$httpClient->getResponse());
      }
      if (isset($result['errcode']) && $result['errcode']!=0) {
        throw new ApiException($result['errmsg'], $result['errcode']);  //非0状态码
      }
      return false;
    }
    //写入文件
    if (empty($toFile)) {
      if (!is_dir(self::getResRoot().'/media')) {
          mkdir(self::getResRoot().'/media');
          chmod(self::getResRoot().'/media', 0777);
      }
      $toFile = self::getResRoot()."/media/$mediaId".$this->parseExtension($header['Content-Type']);
    }
    file_put_contents($toFile, $httpClient->getResponse());
    return filesize($toFile);
  }

  private function parseExtension($contentType) {
    $contentType = strtolower($contentType);
    if ($contentType=='image/jpeg')   return '.jpg';
    if ($contentType=='audio/amr')    return '.mp3';
    if ($contentType=='video/mp4')    return '.mp4';
    return '';
  }

  //通过文件后缀名判断媒体类型
  private function parseMediaType($fileName) {
    $ext = substr($fileName, strripos($fileName, '.')+1);
    $ext = strtolower($ext);
    switch ($ext) {
      case 'png':   return 'image'; break;
      case 'jpeg':  return 'image'; break;
      case 'jpg':   return 'image'; break;
      case 'mp3':   return 'voice'; break;
      case 'amr':   return 'voice'; break;
      case 'mp4':   return 'video'; break;
      default:      return 'image'; break;
    }
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

  //获取默认resource目录
  private static function getResRoot() {
    return defined('RES_ROOT')? RES_ROOT: \wlight\dev\Config::get('RES_ROOT');
  } 
}
?>