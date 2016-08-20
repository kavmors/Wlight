<?php
/**
 * 临时素材管理接口
 * http://mp.weixin.qq.com/wiki/5/963fc70b80dc75483a271298a76a8d59.html
 * http://mp.weixin.qq.com/wiki/11/07b6b76a6b6e8848e855a435d5e34a5f.html
 * @author  KavMors(kavmors@163.com)
 *
 * string upload(string, string)
 * integer download(string, string)
 */

namespace wlight\media;
use wlight\basic\AccessToken;
use wlight\util\HttpClient;
use wlight\runtime\ApiException;

include_once (DIR_ROOT.'/wlight/library/api/basic/AccessToken.class.php');
include_once (DIR_ROOT.'/wlight/library/util/HttpClient.class.php');
include_once (DIR_ROOT.'/wlight/library/runtime/ApiException.class.php');

class Media {
  private $url = 'https://api.weixin.qq.com/cgi-bin/media';
  private $accessToken;

  /**
   * @throws ApiException
   */
  public function __construct() {
    $accessToken = new AccessToken();
    $this->accessToken = $accessToken->get();
  }

  /**
   * 上传一个素材
   * @param string $mediaFile 完整(绝对路径)文件路径
   * @param string $type 可选,上传媒体文件的类型(image、voice、video、thumb),不填则根据后缀名判断
   * @return string mediaId媒体id(请求失败返回false)
   * @throws ApiException
   */
  public function upload($mediaFile, $type=null) {
    if (!file_exists($mediaFile)) {
      throw ApiException::throws(ApiException::FILE_NOT_EXISTS_ERROR_CODE, "file: $mediaFile");
      return false;
    }
    if ($type==null) {
      $type = $this->parseMediaType($mediaFile);
    }
    $url = $this->url.'/upload?access_token='.$this->accessToken.'&type='.$type;
    $httpClient = new HttpClient($url);
    $httpClient->upload(array('media'=>$mediaFile));
    $result = $httpClient->jsonToArray();

    if (isset($result['media_id'])) {
      return $result['media_id'];
    } else {
      throw ApiException::throws(ApiException::ERROR_JSON_ERROR_CODE, 'response: '.$httpClient->getResponse());
    }

    //never
    return false;
  }

  /**
   * 下载一个素材
   * @param string $mediaId 媒体id,通过上传获得
   * @param string $toFile 可选,下载到文件的绝对路径,不填则默认路径为RES_ROOT/$mediaId
   * @return integer 下载到的文件大小(失败时返回false)
   * @throws ApiException
   */
  public function download($mediaId, $toFile=null) {
    //使用http协议
   // $this->url = str_replace('https', 'http', $this->url);
    $url = $this->url.'/get?access_token='.$this->accessToken.'&media_id='.$mediaId;
    $httpClient = new HttpClient($url);
    $httpClient->get(30);

    if ($httpClient->getStatus()!=200 || $httpClient->getResponse()=='') {
      throw ApiException::throws(ApiException::HTTP_ERROR_CODE, 'status code: '.$httpClient->getStatus());
      return false;
    }
    $header = $httpClient->getHeader();
    $contentType = $header['Content-Type'][0];

    //Content-Type为text/时表示带有errcode错误信息
    if (!isset($header['Content-Type']) || stripos($contentType, 'text')!==false) {
      $result = json_decode($httpClient->getResponse(), true);
      if (!$result) {
        throw ApiException::throws(ApiException::JSON_DECODE_ERROR_CODE, 'response: '.$httpClient->getResponse());
      }
      if (isset($result['errcode']) && $result['errcode']!=0) {
        throw new ApiException($result['errmsg'], $result['errcode']);  //非0状态码
      }
      return false;
    }
    //写入文件
    if (empty($toFile)) {
      if (!is_dir(RES_ROOT.'/media')) {
          mkdir(RES_ROOT.'/media');
          chmod(RES_ROOT.'/media', 0775);
      }
      $toFile = RES_ROOT."/media/$mediaId".$this->parseExtension($contentType);
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
}
?>