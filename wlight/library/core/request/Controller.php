<?php
/**
 * 执行消息请求逻辑
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\core;
use wlight\runtime\Log;

include (DIR_ROOT.'/wlight/library/core/request/Response.php');

class Controller {
  const EMPTY_RESPONSE = 'success';
  private $postClass;
  private $cacheQueue = array();

  //Constructor解析xml参数
  public function __construct($postXml) {
    if (!empty($postXml)) {
      $this->postClass = $this->parseXml($postXml);
      $this->postClass = json_decode(json_encode($this->postClass), true);
      foreach ($this->postClass as $key => $value) {
        $this->postClass[$key] = strval($value);
      }

      //注入日志
      Log::getInstance()->markMsgType($this->postClass['MsgType']);
      if ($this->postClass['MsgType']=='text') {
        Log::getInstance()->markContent($this->postClass['Content']);
      } elseif ($this->postClass['MsgType']=='event' && $this->postClass['Event']=='CLICK') {
        Log::getInstance()->markContent($this->postClass['EventKey']);
      }
    }
  }

  //逻辑处理主入口
  public function action() {
    if ($this->postClass==null) {
      return self::EMPTY_RESPONSE;
    }

    $this->lock();

    $target = false;   //先定空返回值
    $phps = $this->getFromCache();
    //针对有缓存情况执行,若【无缓存】或【缓存无效】应返回$target==false
    $target = $this->ergodicPhps($phps);

    //没有缓存或缓存不符合(即旧缓存无效)
    if (empty($target)) {
      $list = $this->getFromList(); //提取所有php文件
      if (is_array($phps)) {        //有缓存,但缓存无效
        //在所有php文件中删除,避免重复检验
        if (($listKey = array_search($phps[0], $list))!==false) {
          unset($list[$listKey]);
        }
      }
      //执行所有遍历
      $target = $this->ergodicPhps($list);
    }

    //更新缓存
    $this->updateCacheFile();

    $this->unlock();

    return $this->invokeTarget($target);
  }

  //遍历所有php文件(或缓存提取的),检验(verify)后执行(invoke)
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
        continue;
      }
      //否则导入
      include($phpName);
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
        $this->callStatistics(substr($classPath, strripos($classPath, "\\")+1), $key->tag());
        $this->putToCache($key->cache(), basename($phpName));
        return $key;
      }
    }
    return false;
  }

  //执行目标类中的invoke方法
  private function invokeTarget($target) {
    if (!$target) {
      return self::EMPTY_RESPONSE;
    }
    $reply = null;
    $reply = $target->invoke();
    $target = null;
    return $reply? $reply: self::EMPTY_RESPONSE;
  }

  //存入缓存, 仅针对类型为text和event.CLICK的消息缓存
  private function putToCache($shouldCache, $phpName) {
    //检测类型
    if ($this->postClass['MsgType']=='text') {
      $key = $this->postClass['Content'];
    } elseif ($this->postClass['MsgType']=='event' && $this->postClass['Event']=='CLICK') {
      $key = $this->postClass['EventKey'];
    } else {
      return false;
    }
    //是否需要缓存
    if ($shouldCache) {
      $this->cacheQueue = array_merge(array($key=>$phpName), $this->cacheQueue);
      //控制缓存最大数量
      while (count($this->cacheQueue)>MAX_CACHE) {
        array_pop($this->cacheQueue);
      }
    }
  }

  //仅提取类型为text和event.CLICK的缓存
  private function getFromCache() {
    //检测类型
    if ($this->postClass['MsgType']=='text') {
      $file = RUNTIME_ROOT.'/cache/msg_text.json.php';
      $key = $this->postClass['Content'];
    } elseif ($this->postClass['MsgType']=='event' && $this->postClass['Event']=='CLICK') {
      $file = RUNTIME_ROOT.'/cache/msg_click.json.php';
      $key = $this->postClass['EventKey'];
    } else {
      return false;
    }
    //检测文件存在
    if (!file_exists($file)) {
      file_put_contents($file, '<?php exit; ?>');
      return false;
    }
    $fileContent = file_get_contents($file);
    $jsonStr = substr($fileContent, stripos($fileContent, '?>')+2);
    $this->cacheQueue = json_decode($jsonStr, true);

    //解析出错则重置缓存文件
    if (empty($this->cacheQueue)) {
      $this->cacheQueue = array();
      file_put_contents($file, '<?php exit; ?>');
      return false;
    }

    if (isset($this->cacheQueue[$key])) {
      $phpName = $this->cacheQueue[$key];
      unset($this->cacheQueue[$key]);

      //类型检测拼接路径
      if ($this->postClass['MsgType']=='text') {
        $phpName = MSG_ROOT.'/text/'.$phpName;
      } elseif ($this->postClass['MsgType']=='event' && $this->postClass['Event']=='CLICK') {
        $phpName = MSG_ROOT.'/event/CLICK/'.$phpName;
      } else {
        return false;
      }
      return array($phpName);
    } else {
      return false;
    }
  }

  //将最新缓存写入文件
  private function updateCacheFile() {
    //检测类型
    if ($this->postClass['MsgType']=='text') {
      $file = RUNTIME_ROOT.'/cache/msg_text.json.php';
    } elseif ($this->postClass['MsgType']=='event' && $this->postClass['Event']=='CLICK') {
      $file = RUNTIME_ROOT.'/cache/msg_click.json.php';
    } else {
      return;
    }
    //写入
    file_put_contents($file, '<?php exit; ?>'.json_encode($this->cacheQueue));
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
    return simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
  }

  //调用统计
  private function callStatistics($className, $tag) {
    if (!is_string($tag)) {
      return;
    }

    //注入日志
    Log::getInstance()->markTag($className.' / '.$tag);

    include (DIR_ROOT.'/wlight/library/statistics/Tag.class.php');
    $statistics = new \wlight\sta\Tag();
    $statistics->increase($className, $tag);
  }

  //文件锁
  private $locker;

  private function lock() {
    $this->locker = fopen(LOCK_CACHE, 'r');
    flock($this->locker, LOCK_EX);
  }

  private function unlock() {
    flock($this->locker, LOCK_UN);
    fclose($this->locker);
  }
}
?>