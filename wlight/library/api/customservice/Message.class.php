<?php
/**
 * 客服接口发消息实现类
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\customservice;
use wlight\basic\AccessToken;
use wlight\util\HttpClient;
use wlight\runtime\ApiException;

class Message {
  private $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=';
  private $accessToken;
  private $postfix;
  private $account;

  /**
   * @throws ApiException
   */
	public function __construct() {
    include_once (DIR_ROOT.'/wlight/library/api/basic/AccessToken.class.php');
    include_once (DIR_ROOT.'/wlight/library/util/HttpClient.class.php');
    include_once (DIR_ROOT.'/wlight/library/runtime/ApiException.class.php');

    $accessToken = new AccessToken();
    $this->accessToken = $accessToken->get();
    $this->postfix = WECHAT_ID;
	}

  /**
   * 指定发送消息的客服帐号
   * @param string $account - 客服帐号
   */
  public function setAccount($account) {
    $this->account = $this->addPostfix($account);
  }

  /**
   * 发送文本消息
   * @param string $user - 接收方
   * @param string $text - 文本消息
   * @return boolean - true表示成功
   * @throws ApiException
   */
  public function sendText($user, $text) {
    $json = array(
      'touser' => $user,
      'msgtype' => 'text',
      'text' => array(
        'content' => urlencode($text)
      )
    );
    $json = $this->addAccount($json);
    return $this->sendToTarget($json);
  }

  /**
   * 发送图片消息
   * @param string $user - 接收方
   * @param string $mediaId - 图片媒体id
   * @return boolean - true表示成功
   * @throws ApiException
   */
  public function sendImage($user, $mediaId) {
    $json = array(
      'touser' => $user,
      'msgtype' => 'image',
      'image' => array(
        'media_id' => $mediaId
      )
    );
    $json = $this->addAccount($json);
    return $this->sendToTarget($json);
  }

  /**
   * 发送语音消息
   * @param string $user - 接收方
   * @param string $mediaId - 语音媒体id
   * @return boolean - true表示成功
   * @throws ApiException
   */
  public function sendVoice($user, $mediaId) {
    $json = array(
      'touser' => $user,
      'msgtype' => 'voice',
      'voice' => array(
        'media_id' => $mediaId
      )
    );
    $json = $this->addAccount($json);
    return $this->sendToTarget($json);
  }

  /**
   * 发送视频消息
   * @param string $user - 接收方
   * @param string $mediaId - 视频媒体id
   * @param string $thumbMediaId - 缩略图媒体id
   * @param string $title - 可选,视频标题
   * @param string $description - 可选,视频描述
   * @return boolean - true表示成功
   * @throws ApiException
   */
  public function sendVideo($mediaId, $thumbMediaId, $title='', $description='') {
    $json = array(
      'touser' => $user,
      'msgtype' => 'video',
      'video' => array(
        'media_id' => $mediaId,
        'thumb_media_id' => $thumbMediaId,
        'title' => urlencode($title),
        'description' => urlencode($description)
      )
    );
    $json = $this->addAccount($json);
    return $this->sendToTarget($json);
  }

  /**
   * 发送音乐消息
   * @param string $user - 接收方
   * @param string $title - 音乐标题
   * @param string $description - 音乐描述
   * @param string $musicUrl - 音乐链接
   * @param string $hqMusicUrl - 音乐高品质资源链接
   * @param string $thumbMediaId - 缩略图媒体id
   * @return boolean - true表示成功
   * @throws ApiException
   */
  public function sendMusic($title, $description, $musicUrl, $hqMusicUrl, $thumbMediaId) {
    $json = array(
      'touser' => $user,
      'msgtype' => 'music',
      'music' => array(
        'title' => urlencode($title),
        'description' => urlencode($description),
        'musicurl' => $musicUrl,
        'hqmusicurl' => $hqMusicUrl,
        'thumb_media_id' => $thumbMediaId,
      )
    );
    $json = $this->addAccount($json);
    return $this->sendToTarget($json);
  }

  /**
   * 发送图文消息(跳转到链接)
   * @param string $user - 接收方
   * @param array $articles - (二维数组)图文内容, 包含字段: Title, Description, PicUrl, Url
   * @example array(
   *      array('Title'=>'1', 'Description'=>'', 'PicUrl'=>'1.jpg', 'Url'=>''))
   * @return boolean - true表示成功
   * @throws ApiException
   */
  public function sendNews($user, $articles) {
    if (isset($articles['Title'])) {
      $articles = array($articles);
    }
    foreach ($articles as $item) {
      $item['Title'] = urlencode($item['Title']);
      $item['Description'] = urlencode($item['Description']);
    }
    $json = array(
      'touser' => $user,
      'msgtype' => 'news',
      'news' => array(
        'articles' => $articles
      )
    );
    $json = $this->addAccount($json);
    return $this->sendToTarget($json);
  }

  /**
   * 发送图文消息(跳转到图文页面)
   * @param string $user - 接收方
   * @param string $mediaId - 图文媒体id
   * @return boolean - true表示成功
   * @throws ApiException
   */
  public function sendMpnews($user, $mediaId) {
    $json = array(
      'touser' => $user,
      'msgtype' => 'mpnews',
      'mpnews' => array(
        'media_id' => $mediaId
      )
    );
    $json = $this->addAccount($json);
    return $this->sendToTarget($json);
  }

  /**
   * 发送卡券
   * @param string $user - 接收方
   * @param string $cardId - 卡券id
   * @param array $cardExt - 卡券card_ext字段信息
   * @return boolean - true表示成功
   * @throws ApiException
   */
  public function sendCard($user, $cardId, $cardExt) {
    $json = array(
      'touser' => $user,
      'msgtype' => 'wxcard',
      'wxcard' => array(
        'card_id' => $cardId,
        'card_ext' => $cardExt
      )
    );
    $json = $this->addAccount($json);
    return $this->sendToTarget($json);
  }

  //发送到目标url
  private function sendToTarget($jsonArr) {
    $url = $this->url.$this->accessToken;
    $httpClient = new HttpClient($url);
    $httpClient->setBody(urldecode(json_encode($jsonArr)));
    $httpClient->post();
    $result = $httpClient->jsonToArray();

    if (isset($result['errcode']) && $result['errcode']==0) {
      return true;
    } else {
      throw ApiException::errorJsonException('response: '.$httpClient->getResponse());
    }

    //never
    return false;
  }

  //为json数据包添加客服字段
  private function addAccount($json) {
    if ($this->account) {
      $json['customservice'] = array('kf_account'=>$this->account);
    }
    return $json;
  }

  //为未添加后缀的客服帐号添加后缀
  private function addPostfix($account) {
    $at = stripos($account, '@');
    if ($at===false) {
      return $account.'@'.$this->postfix;
    } else {
      return $account;
    }
  }
}
?>