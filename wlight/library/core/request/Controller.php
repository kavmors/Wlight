<?php
/**
 * 执行消息请求逻辑
 * @author  KavMors(kavmors@163.com)
 */

namespace wlight\core;
use wlight\runtime\Log;
use wlight\util\DbHelper;

include (DIR_ROOT.'/wlight/library/core/request/Response.php');
include (DIR_ROOT.'/wlight/library/core/support/CacheController.class.php');
include (DIR_ROOT.'/wlight/library/core/support/Statis.class.php');

class Controller {
  private $postClass;
  private $cacheController;
  private $statistics;

  //Constructor解析xml参数
  public function __construct($postXml) {
    if (!empty($postXml)) {
      $this->postClass = $this->parseXml($postXml);
      foreach ($this->postClass as $key => $value) {
        if (empty($value)) {
          $value = '';
        }
        $this->postClass[$key] = strval($value);
      }

      Log::createTime($this->postClass['CreateTime']);
      Log::receive($postXml);

      //Cache init
      $this->cacheController = new support\CacheController;

      //Hook init
      $hook = MSG_ROOT.'/Hook.php';
      if (file_exists($hook)) {
        include ($hook);
        @$this->hook = new \wlight\msg\Hook;
      }
    }
  }

  //逻辑处理主入口
  public function action() {
    if ($this->postClass == null) {
      return Response::EMPTY_RESPONSE;
    }

    //从排重缓存中获取超时缓存消息
    $retry = 7;
    while ($retry--) {
      $memCache = $this->cacheController->getRetry($this->cacheController->keyRetry($this->postClass));
      if (!empty($memCache)) {    //返回字串执行结果
        Log::cancel();
        return $memCache;
      } elseif ($memCache === '') {   //有键无值,首次执行还没结束,直接等待下一次请求
        Log::cancel();
        sleep(1);
      } else {
        break;
      }
    }
    if ($retry <= 0) {    //超时等待下一次重连
      return '';
    }

    //在排重缓存中记录缓存值,防止第二次请求重新触发
    $this->cacheController->putRetry($this->postClass['CreateTime'], $this->cacheController->keyRetry($this->postClass), '');

    //在所有自动回复前hook
    if ($this->hook) {
      @$this->hook->onPreExecute($this->postClass);
    }

    $target = false;   //先定空返回值
    $phps = $this->getFromCache();
    //针对有缓存情况执行,若[无缓存]或[缓存无效],返回$target==false
    $target = $this->ergodicPhps($phps);

    //没有缓存或缓存不符合(即旧缓存无效)
    if (empty($target)) {
      $list = $this->getFromList(); //提取所有php文件
      if (is_array($phps)) {        //有缓存但缓存无效
        //在所有php文件中删除,避免重复检验
        if (($listKey = array_search($phps[0], $list))!==false) {
          unset($list[$listKey]);
        }
      }
      //执行所有遍历
      $target = $this->ergodicPhps($list);
    }
    $this->callMessage($this->postClass);

    $result = $this->invokeTarget($target);
    $target = null;

    //在所有自动回复后hook
    if ($this->hook) {
      @$this->hook->onPostExecute($result);
    }

    //在排重缓存中记录已执行完成的结果值,在下次请求时直接返回
    $this->cacheController->putRetry($this->postClass['CreateTime'], $this->cacheController->keyRetry($this->postClass), $result);

    Log::response($result);
    return $result;
  }

  //遍历所有php文件,检验(verify)后执行(invoke)
  //若检验后无一符合,返回false
  private function ergodicPhps($phps) {
    //先检验$phps的数组性质
    if (!(is_array($phps) && count($phps)>0)) {
      return false;
    }

    //主体逻辑
    foreach ($phps as $phpName) {
      if (!file_exists($phpName)) {
        continue;
      }

      $classPath = $this->getClassPath($phpName);

      //无法解析类名跳过
      if ($classPath==false) {
        continue;
      }

      //类已存在则跳过
      if (class_exists($classPath)) {
        include_once($phpName);
      } else {
        include($phpName);
      }

      //仍不存在该类则跳过
      if (!class_exists($classPath)) {
        continue;
      }

      $key = new $classPath;
      //检测$key父类类型
      if (!is_subclass_of($key, '\wlight\core\Response')) {
        continue;
      }
      //赋值传递参数
      $key->assign($this->postClass);
      //验证是否执行
      if ($key->verify()) {
        $this->callTag(substr($classPath, strripos($classPath, "\\")+1), $key->tag());
        $this->putToCache($key->cache(), basename($phpName));
        return $key;
      }
    }
    return false;
  }

  //执行目标类中的invoke方法
  private function invokeTarget($target) {
    if (!$target) {
      return Response::EMPTY_RESPONSE;
    }
    $reply = null;
    $reply = $target->invoke();
    return $reply ? $reply: Response::EMPTY_RESPONSE;
  }

  //存入缓存
  private function putToCache($shouldCache, $phpName) {
    $key = $this->cacheController->keyMsg($this->postClass);
    $type = $this->postClass['MsgType'];
    $target = $shouldCache ? $phpName : '';
    $this->cacheController->putMsg($this->postClass['CreateTime'], $type, $key, $target);
  }

  //提取缓存
  private function getFromCache() {
    $cache = $this->cacheController->getMsg($this->postClass['MsgType'], $this->cacheController->keyMsg($this->postClass));
    if ($cache == '') {
      return false;
    }
    return array($this->cacheController->getPathByType($this->postClass)."/$cache");
  }

  //列出所有php类文件
  private function getFromList() {
    //根据MsgType选取目录
    if ($this->postClass['MsgType'] == 'event') {
      $phps = $this->listPhpFiles('event/'.$this->postClass['Event']);
    } else {
      $phps = $this->listPhpFiles($this->postClass['MsgType']);
    }
    return $phps;
  }

  //列出MSG_ROOT中子目录的所有php文件
  private function listPhpFiles($subdir) {
    $dir = MSG_ROOT."/$subdir";
    if (is_dir($dir)) {
      return glob($dir.'/*.php');
    } else {
      return false;
    }
  }

  //从文件名解析对应类名
  private function getClassPath($phpName) {
    $className = basename($phpName);
    $className = substr($className, 0, strripos($className, '.'));

    //类名规则:以字母开头,其他仅包含字母、数字、下划线
    if (preg_match("/[A-Za-z]+(\\w)*/", $className, $matches)) {
      $className = $matches[0];
      return "\\wlight\\msg\\$className";
    } else {
      return false;
    }
  }

  //转换xml为stdClass对象
  private function parseXml($postStr) {
    return json_decode(json_encode(simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
  }

  //调用统计
  private function callTag($className, $tag) {
    $this->statistics = new support\Statis;
    if (!is_string($tag) || $tag == '') {
      return;
    }
    if ($this->statistics->isReady()) {
      $this->statistics->increase($className, $tag);
    }
  }

  //调用留言
  private function callMessage($postClass) {
    if ($this->statistics != null) {
      return;
    }
    $this->statistics = new support\Statis;
    if ($this->statistics->isReady()) {
      $this->statistics->insertMessage($postClass);
    }
  }
}
?>