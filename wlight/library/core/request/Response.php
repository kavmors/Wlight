<?php
/**
 * 消息逻辑处理的基本类
 * 集成此类并覆盖相关方法, 以完成验证和消息回复
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\core;

class Response {
  const TYPE_TEXT = 'text';
  const TYPE_IMAGE = 'image';
  const TYPE_VOICE = 'voice';
  const TYPE_VIDEO = 'video';
  const TYPE_SHORTVIDEO = 'shortvideo';
  const TYPE_LOCATION = 'location';
  const TYPE_LINK = 'link';

  const TYPE_EVENT = 'event';
  const TYPE_EVENT_SUBSCRIBE = 'subscribe';
  const TYPE_EVENT_UNSUBSCRIBE = 'unsubscribe';
  const TYPE_EVENT_SCAN = 'SCAN';
  const TYPE_EVENT_LOCATION = 'LOCATION';
  const TYPE_EVENT_CLICK = 'CLICK';
  const TYPE_EVENT_VIEW = 'VIEW';

  protected $map;

  //验证是否执行invoke方法中的逻辑, 返回true表示执行
  public function verify() {
    return false;
  }

  //执行逻辑并返回待回复的消息
  //回复消息必须经过makeX系列方法处理
  public function invoke() {
    return $this->emptyResponse();
  }

  /**
   * 当前操作的标签, 用于统计当前操作的使用次数
   * 返回array时, 包含统计键值和操作名称
   * 返回string时候, 默认使用相同的统计键值和操作名称
   * 返回null表示当前操作不需要加入统计
   */
  public function tag() {
    return null;
  }

  /**
   * 标识当前消息是否需要加入缓存
   * 返回true时表示需要缓存
   * 默认为true
   */
  public function cache() {
    return true;
  }

  public final function assign($postClass) {
    $this->map = $postClass;
  }
  
  /**
   * 引入一个类库文件,并返回该类的完整类名(包含命名空间)
   * @param $namespace - 类所在的空间
   * @param $className - 类名
   * @return 完整的命名空间+类名
   * @example 引用AccessToken类时:
   *          $class = $this->import('basic', 'AccessToken');
   *          $accessToken = (new $class())->get()
   */
  protected final function import($namespace, $className) {
    $wholeClass = "\\wlight\\$namespace\\$className";
    //除了util外,其余类库在/api内
    if ($namespace!='util') {
      $namespace = "api/$namespace";
    }
    include_once(DIR_ROOT."/wlight/library/$namespace/$className.class.php");
    $instance = new $wholeClass();
    return $instance;
  }

  protected final function emptyResponse() {
    return 'success';
  }

  /**
   * 封装文本消息
   * @param string $text
   * @return string - response xml
   */
  protected final function makeText($text) {
    $msgType = 'text';
    $createTime = time();
    $response = "<xml>
          <ToUserName><![CDATA[%s]]></ToUserName>
          <FromUserName><![CDATA[%s]]></FromUserName>
          <CreateTime>%s</CreateTime>
          <MsgType><![CDATA[%s]]></MsgType>
          <Content><![CDATA[%s]]></Content>
          </xml>";
    $reply = sprintf($response, $this->map['FromUserName'], $this->map['ToUserName'], $createTime, $msgType, $text);
    return $reply;
  }

  /**
   * 封装图片消息
   * @param integer $mediaId
   * @return string - response xml
   */
  protected final function makeImage($mediaId) {
    $createTime = time();
    $msgType = 'image';
    $response = "<xml>
          <ToUserName><![CDATA[%s]]></ToUserName>
          <FromUserName><![CDATA[%s]]></FromUserName>
          <CreateTime>%s</CreateTime>
          <MsgType><![CDATA[%s]]></MsgType>
          <Image>
          <MediaId><![CDATA[%s]]></MediaId>
          </Image>
          </xml>";
    $reply = sprintf($response, $this->map['FromUserName'], $this->map['ToUserName'], $createTime, $msgType, $mediaId);
    return $reply;
  }

  /**
   * 封装语音消息
   * @param integer $mediaId
   * @return string - response xml
   */
  protected final function makeVoice($mediaId) {
    $createTime = time();
    $msgType = 'voice';
    $response = "<xml>
          <ToUserName><![CDATA[%s]]></ToUserName>
          <FromUserName><![CDATA[%s]]></FromUserName>
          <CreateTime>%s</CreateTime>
          <MsgType><![CDATA[%s]]></MsgType>
          <Voice>
          <MediaId><![CDATA[%s]]></MediaId>
          </Voice>
          </xml>";
    $reply = sprintf($response, $this->map['FromUserName'], $this->map['ToUserName'], $createTime, $msgType, $mediaId);
    return $reply;
  }

  /**
   * 封装视频消息
   * @param string $mediaId
   * @return string - response xml
   */
  protected final function makeVideo($mediaId, $title='', $description='') {
    $createTime = time();
    $msgType = 'video';
    $response = "<xml>
          <ToUserName><![CDATA[%s]]></ToUserName>
          <FromUserName><![CDATA[%s]]></FromUserName>
          <CreateTime>%s</CreateTime>
          <MsgType><![CDATA[%s]]></MsgType>
          <Video>
          <MediaId><![CDATA[%s]]></MediaId>
          <Title><![CDATA[%s]]></Title>
          <Description><![CDATA[%s]]></Description>
          </Video> 
          </xml>";
    $reply = sprintf($response, $this->map['FromUserName'], $this->map['ToUserName'], $createTime, $msgType, $mediaId, $title, $description);
    return $reply;
  }

  /**
   * 封装音乐消息
   * @param string $title
   * @param string $description
   * @param string $musicUrl
   * @param string $hqMusicUrl
   * @param integer $thumbMediaId
   * @return string - response xml
   */
  protected final function makeMusic($title, $description, $musicUrl, $hqMusicUrl='', $thumbMediaId='') {
    $createTime = time();
    $msgType = 'music';
    $response = "<xml>
          <ToUserName><![CDATA[%s]]></ToUserName>
          <FromUserName><![CDATA[%s]]></FromUserName>
          <CreateTime>%s</CreateTime>
          <MsgType><![CDATA[%s]]></MsgType>
          <Music>
          <Title><![CDATA[%s]]></Title>
          <Description><![CDATA[%s]]></Description>
          <MusicUrl><![CDATA[%s]]></MusicUrl>
          <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>";
    if ($thumbMediaId!=='') {
      $response .= "<ThumbMediaId><![CDATA[$thumbMediaId]]></ThumbMediaId>";
    }
    $response .= "</Music></xml>";
    $reply = sprintf($response, $this->map['FromUserName'], $this->map['ToUserName'], $createTime, $msgType, $title, $description, $musicUrl, $hqMusicUrl);
    return $reply;
  }

  /**
   * 封装图文消息
   * @param array $articles - (二维数组)图文内容, 包含字段: Title, Description, PicUrl, Url
   * @example array(
   *      array('Title'=>'1', 'Description'=>'', 'PicUrl'=>'1.jpg', 'Url'=>''))
   * @return string - response xml
   */
  protected final function makeNews($articles) {
    if (isset($articles['Title'])) $articles = array($articles);
    $createTime = time();
    $msgType = 'news';
    $count = count($articles);
    $strItem = '';
    if (is_array($articles)) {
      foreach ($articles as $item) {
        if (is_array($item)) {
          $temp = "<item>
              <Title><![CDATA[%s]]></Title> 
              <Description><![CDATA[%s]]></Description>
              <PicUrl><![CDATA[%s]]></PicUrl>
              <Url><![CDATA[%s]]></Url>
              </item>";
          $strItem .= sprintf($temp, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
      }
    }
    $response = "<xml>
          <ToUserName><![CDATA[%s]]></ToUserName>
          <FromUserName><![CDATA[%s]]></FromUserName>
          <CreateTime>%s</CreateTime>
          <MsgType><![CDATA[%s]]></MsgType>
          <ArticleCount>%s</ArticleCount>
          <Articles>
          %s
          </Articles>
          </xml>";
    $reply = sprintf($response, $this->map['FromUserName'], $this->map['ToUserName'], $createTime, $msgType, $count, $strItem);
    return $reply;
  }

  /**
   * 转到多客服
   * @param string $account - 可选,指定转发到的客服帐号
   * @return string - response xml
   */
  protected final function sendToService($account='') {
    $createTime = time();
    $msgType = 'transfer_customer_service';
    $response = "<xml>
          <ToUserName><![CDATA[%s]]></ToUserName>
          <FromUserName><![CDATA[%s]]></FromUserName>
          <CreateTime>%s</CreateTime>
          <MsgType><![CDATA[%s]]></MsgType>";
    if ($account!=='') {
      //处理account后缀
      if (stripos($account, '@')===false) {
        $account = $account.'@'.WECHAT_ID;
      }
      $response .= "<TransInfo><KfAccount><![CDATA[$account]]></KfAccount></TransInfo>";
    }
    $response .= "</xml>";
    $reply = sprintf($response, $this->map['FromUserName'], $this->map['ToUserName'], $createTime, $msgType);
    return $reply;
  }
}