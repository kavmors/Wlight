<?php
/**
 * 网页授权获取用户信息相关接口
 * http://mp.weixin.qq.com/wiki/4/9ac2e7b1f1d22e9e57260f6553822520.html
 * @author  KavMors(kavmors@163.com)
 *
 * void setUrl(string)
 * void setAction(string)
 * void setExtra(string)
 */

namespace wlight\oauth;
use wlight\util\DbHelper;

include_once (DIR_ROOT.'/wlight/library/util/DbHelper.class.php');

class Oauth {
  private $db;
  private $createTime;
  private $url;
  private $action;
  private $extra;

  private $table;

  /**
   * @throws ApiException
   */
  public function __construct() {
    $helper = new DbHelper;
    $this->db = $helper->getConnector();
    $this->createTime = time();

    $this->table = DB_PREFIX.'_cache_oauth';
  }

  /**
   * 设置重定向url,该url指向的页面通过post参数授权结果(result)和额外信息(extra)
   * @param string $url 重定向url
   */
  public function setUrl($url) {
    $this->url = $url;
  }

  /**
   * 设置授权作用域
   * @param $action 授权作用域,可选basic或info
   */
  public function setAction($action) {
    $this->action = 'snsapi_'.$action;
  }

  /**
   * 添加额外信息
   * @param string $extra 额外信息字符串,授权通过后原样返回
   */
  public function setExtra($extra) {
    $this->extra = $extra;
  }

  /**
   * 通过重定向跳转到授权页面.
   * 若不使用重定向方式跳转,可通过getLocation获取授权url后自行控制跳转
   */
  public function redirect() {
    header('Location: '.$this->getLocation());
  }

  /**
   * 获取授权url
   * @return string 授权url,授权成功后通过post向该url提交数据
   * @throws PDOException
   */
  public function getLocation() {
    $appid = APP_ID;
    $url = HOST.PATH.'/wlight/library/api/oauth/Validate.dist.php';
    $id = $this->order();
    $location = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$url&response_type=code&scope=$this->action&state=$id#wechat_redirect";
    return $location;
  }

  //返回id编号
  private function order() {
    //清理过期缓存
    $max = MAX_CACHE;
    $result = $this->db->query("SELECT `priority` FROM `$this->table` ORDER BY `priority` DESC LIMIT $max, 1");
    $result = $result->fetchAll(\PDO::FETCH_ASSOC);
    if (count($result) != 0) {
      $maxTime = $result[0]['priority'];
      $this->db->exec("DELETE FROM `$this->table` WHERE `priority` <= $maxTime");
    }

    while (1) {
      $id = $this->getRandomStr(10);
      $sql = "INSERT INTO `$this->table` VALUES('$id', '$this->url', '$this->extra', '', $this->createTime)";
      $count = $this->db->exec($sql);
      if ($count != 0) {
        return $id;
      }
    }
  }

  //计算随机字符串
  private function getRandomStr($length) {
    $str = "";
    $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($str_pol) - 1;
    for ($i = 0; $i < $length; $i++) {
      $str .= $str_pol[mt_rand(0, $max)];
    }
    return $str;
  }
}
?>