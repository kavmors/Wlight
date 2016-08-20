<?php
/**
 * 网页授权重定向页面
 * @author  KavMors(kavmors@163.com)
 */

namespace wlight\oauth;
use wlight\dev\Library;
use wlight\util\DbHelper;
use wlight\util\HttpClient;
use wlight\util\ArrayHelper;
use wlight\runtime\ApiException;

include (dirname(__FILE__).'/../../../develop/Library.class.php');

$code = isset($_GET['code']) ? $_GET['code'] : exit();
$id = isset($_GET['state']) ? $_GET['state'] : exit();
$handler = new Validate;

$result = $handler->getCache($id);
if ($result === null) {
  exit('请求信息无效');
} else {
  if ($result != '') {
    $handler->form($result);
  } else {
    try {
      $basic = $handler->requestBasic($code);

      if ($basic['scope'] == 'snsapi_userinfo') {
        $result = $handler->requestInfo($basic['access_token'], $basic['openid']);
      } else {
        $result = array('openid'=>$basic['openid']);
      }

      $result = $handler->toJson($result);
      $handler->setCache($id, $result);
      $handler->form($result);
    } catch (ApiException $e) {
      $err = array(
        'type' => get_class($e),
        'file' => $e->getFile(),
        'code' => $e->getCode(),
        'line' => $e->getLine(),
        'msg' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      );
      $handler->form($handler->toJson($err));
    }
  }
}

class Validate {
  private $url;
  private $extra;
  private $db;
  private $http;
  private $table;
  private $helper;

  public function __construct() {
    $this->db = Library::import('util', 'DbHelper');
    $this->db = $this->db->getConnector();
    $this->table = DB_PREFIX.'_cache_oauth';
    $this->http = Library::import('util', 'HttpClient');
    $this->helper = Library::import('util', 'ArrayHelper');
  }

  /**
   * 数据库获取缓存结果
   */
  public function getCache($id) {
    $result = $this->db->prepare("SELECT `url`, `extra`, `result` FROM `$this->table` WHERE `id` = ?");
    $result->execute(array($id));
    $result = $result->fetchAll(\PDO::FETCH_ASSOC);
    if (count($result) == 0) {
      return null;
    } else {
      $this->url = $result[0]['url'];
      $this->extra = $result[0]['extra'];
      return $result[0]['result'];
    }
  }

  /**
   * 缓存查询结果
   */
  public function setCache($id, $result) {
    $this->db->exec("UPDATE `$this->table` SET `result` = '$result' WHERE `id` = '$id'");
  }

  /**
   * 请求basic数据
   */
  public function requestBasic($code) {
    $appid = APP_ID;
    $appsecret = APP_SECRET;
    $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";

    $this->http->reset();
    $this->http->setUrl($url);
    $this->http->get();
    return $this->http->jsonToArray();
  }

  /**
   * 请求userinfo数据
   */
  public function requestInfo($token, $openid) {
    $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$token&openid=$openid&lang=zh_CN";
    $this->http->reset();
    $this->http->setUrl($url);
    $this->http->get();
    return $this->http->jsonToArray();
  }

  /**
   * 生成表单并提交
   */
  public function form($result) {
    echo "<form name='form' method='post' action='$this->url'>";
    echo "<input type='hidden' name='result' value='$result'>";
    echo "<input type='hidden' name='extra' value='$this->extra'>";
    echo "</form>";
    echo "<script>document.form.submit()</script>";
  }

  public function toJson($a) {
    return $this->helper->toJson($a);
  }
}

?>