<?php
/**
 * 日志记录类库(仅供Wlight库日志用)
 * @author	KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\runtime;

class Log {
  const LOG_ROOT = RUNTIME_ROOT.'/log';
  private static $instance;

  public static function getInstance() {
    if (self::$instance==null) {
      self::$instance = new Log();
    }
    return self::$instance;
  }

  private $log;
  private $error;
  private $msgRecord;   //消息包内容

  private $startTime;
  private $endTime;

  public function __construct() {
    $this->clearExpiredLog();
  }

  //开始记录
  public function start() {
    $this->startTime = microtime(true);
    $this->log = '';
    $this->error = '';
    $this->msgRecord = '';
  }

  //添加log记录
  public function i($tag, $msg='') {
    $this->log .= "\n[ $tag ] $msg";
  }

  //添加error记录
  public function e($tag, $msg='') {
    $this->error .= "[ $tag ] $msg\n";
  }

  //记录消息包内容
  public function markMsgType($type) {
    $this->msgRecord .= "[ MsgType ] $type\n";
  }

  public function markContent($content) {
    $this->msgRecord .= "[ Content ] $content\n";
  }

  public function markTag($tag) {
    $this->msgRecord .= "[ Tag ] $tag\n";
  }

  //结束记录并写入Log
  public function end() {
    $this->endTime = microtime(true);
    $this->writeInfo();
    $this->writeError();
  }

  //写Info类信息
  private function writeInfo() {
    $file = self::LOG_ROOT.'/info/'.$this->escapeToDate().'.log.php';
    if (!file_exists($file)) {
      file_put_contents($file, "<?php exit; ?>\n\n");   //新建log并添加文件头, 防止被访问
      chmod($file, 0777);
    }

    //日志内容
    $info  = '[ '.$this->escapeToTime().' ]';
    $info .= $this->log;
    $info .= "\n";
    $info .= '[ Runtime: '. $this->getExpTime(). 's ]'."\n\n";

    //写入
    file_put_contents($file, $info, FILE_APPEND);
  }

  //写error类信息
  private function writeError() {
    if (empty($this->error)) {
      return;
    }
    $file = self::LOG_ROOT.'/error/'.$this->escapeToDate().'.log.php';
    if (!file_exists($file)) {
      file_put_contents($file, "<?php exit; ?>\n\n");   //新建error并添加文件头, 防止被访问
      chmod($file, 0777);
    }

    //日志内容
    $info  = '[ '.$this->escapeToTime().' ]';
    $info .= "\n";
    $info .= $this->msgRecord;
    $info .= $this->error;
    $info .= "\n";

    //写入
    file_put_contents($file, $info, FILE_APPEND);
  }

  //计算运行时间
  private function getExpTime() {
    return sprintf("%.6f", $this->endTime - $this->startTime);
  }

  //转换为日期
  private function escapeToDate($time=null) {
    if ($time==null) {
      $time = $this->startTime;
    }
    return date('Y-m-d', intval($time));
  }

  //转换为时间
  private function escapeToTime($time=null) {
    if ($time==null) {
      $time = $this->startTime;
    }
    return date('H:i:s', intval($time));
  }

  //清理过期日志
  private function clearExpiredLog() {
    $expireTime = intval(LOG_LIVE) * 24;  //计算过期时间对应的小时
    $expireTime = '-'.strval($expireTime).' hour';
    $expireTime = strtotime($expireTime, time());

    $infoDir = RUNTIME_ROOT.self::LOG_ROOT.'/info';
    $errorDir = RUNTIME_ROOT.self::LOG_ROOT.'/error';
    
    //列出所有log文件(.log.php)
    if (is_dir($infoDir)) {
      $infoDir = asort(glob($infoDir.'/*.log.php'));
      if (is_array($infoDir)) {
        foreach ($infoDir as $file) {
          $fileName = basename($file);
          $fileName = substr($fileName, 0, stripos($fileName, '.'));  //提取日期
          if (strtotime($fileName)<$expireTime) {   //文件日期<过期日期时,删除日志文件
            unlink($file);
          } else {                 //不满足文件日期<过期日期的文件不删除(排序后)
            break;
          }
        }
      }
    }

    if (is_dir($errorDir)) {
      $errorDir = asort(glob($errorDir.'/*.log.php'));
      if (is_array($errorDir)) {
        foreach ($errorDir as $file) {
          $fileName = basename($file);
          $fileName = substr($fileName, 0, stripos($fileName, '.'));  //提取日期
          if (strtotime($fileName)<$expireTime) {   //文件日期<过期日期时,删除日志文件
            unlink($file);
          } else {                 //不满足文件日期<过期日期的文件不删除(排序后)
            break;
          }
        }
      }
    }
  }
}
?>