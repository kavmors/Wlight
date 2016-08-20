<?php
/**
 * 日志记录类库
 * @author  KavMors(kavmors@163.com)
 */

namespace wlight\runtime;
use wlight\core\support\RecordManager;


class Log {
  private static $startTime;
  private static $isCancel = false;
  private static $buffer = array();
  private static $errBuffer = array();
  private static $warning = null;

  private static $file;

  /**
   * 脚本开始,初始化Log
   */
  public static function start() {
    self::$startTime = microtime(true);
    self::$file = RUNTIME_ROOT.'/log/'.date('Y-m-d').'.log.php';
    if (!file_exists(self::$file)) {
      self::initFile(self::$file);
    }
  }

  /**
   * 记录本次请求操作为verify
   */
  public static function verify() {
    self::i('Action', "\t\tverify");
  }

  /**
   * 记录本次请求操作为reply
   */
  public static function reply() {
    self::i('Action', "\t\treply");
  }

  /**
   * 记录本次请求的时间(来自微信服务器的时间)
   * @param string $time
   */
  public static function createTime($time) {
    $time = date('Y-m-d H:i:s', $time);
    self::i('CreateTime', $time);
  }

  /**
   * 记录剩余接收信息(微信id,消息类型,时间除外)
   */
  public static function receive($xml) {
    $xml = str_ireplace(array("<![CDATA[", "]]>"), "", $xml);
    $xml = str_ireplace("><", ">\n  <", $xml);
    self::i('Receive', "  ".$xml);
  }

  /**
   * 记录剩余回复信息(微信id,消息类型,时间除外)
   */
  public static function response($xml) {
    $xml = str_ireplace(array("<![CDATA[", "]]>"), "", $xml);
    $xml = str_ireplace(">\n<", ">\n  <", $xml);
    self::i('Response', "  ".$xml);
  }

  /**
   * 本次请求不记录在log
   */
  public static function cancel() {
    self::$isCancel = true;
  }

  /**
   * 结束脚本,将log写入文件
   */
  public static function end() {
    if (!self::$isCancel) {
      self::i('Runtime', sprintf("\t\t%.5f (s)", microtime(true)-self::$startTime));
      $content = implode("\n", self::$buffer);
      if (count(self::$errBuffer) != 0) {
        $content .= "\n------------------------------------------------ERROR------------------------------------------------\n";
        $content .= implode("\n", self::$errBuffer);
      }
      if (self::$warning != null) {
        $content .= "\n-----------------------------------------------WARNING-----------------------------------------------\n";
        $content .= self::$warning;
      }
      file_put_contents(self::$file, "\n\n\n".$content, FILE_APPEND);
    }
  }

  /**
   * 记录请求信息log
   * @param string $tag log标签
   * @param string $message log信息
   * @param boolean $pushFirst true表示将本次log的信息置顶
   */
  public static function i($tag, $message, $pushFirst=false) {
    if (strpos($message, "\n") === false) {
      $content = "[$tag]\t$message";
    } else {
      $content = "[$tag]------------------------------\n$message";
    }
    if ($pushFirst) {
      array_unshift(self::$buffer, $content);
    } else {
      self::$buffer[] = $content;
    }
  }

  /**
   * 添加exception的log(error)
   * @param Exception $e
   * @param array $extra
   */
  public static function e($e, $extra=null) {
    $type = get_class($e);
    $file = $e->getFile();
    $code = $e->getCode();
    $line = $e->getLine();
    $msg = $e->getMessage();
    $trace = $e->getTraceAsString();

    $message = "  [Code]\t\t$code\n";
    $message .= "  [Message]\t$msg\n";
    $message .= "  [At]\t\t\t$file(Line $line)\n";
    if (is_array($extra)) {
      foreach ($extra as $key => $value) {
        $message .= "  [$key]\t\t$value\n";
      }
    }
    $message .= '  '.str_ireplace("\n", "\n  ", $trace);

    $content = "[$type]------------------------------\n$message";
    self::$errBuffer[] = $content;
  }

  /**
   * 记录脚本执行时输出的warning内容
   * @param string $buffer 脚本输出错误信息
   */
  public static function w($buffer) {
    self::$warning = $buffer;
  }

  //log文件不存在时初始化
  private static function initFile($file) {
    file_put_contents($file, "<?php exit; ?>");
    self::clearExpiredLog();
  }

  //清理过期日志
  private static function clearExpiredLog() {
    $expireTime = '-'.LOG_LIVE.' day';
    $expireDate = date('Y-m-d', strtotime($expireTime, time()));

    //列出所有log文件(.log.php)
    if (is_dir(RUNTIME_ROOT.'/log')) {
      $logs = glob(RUNTIME_ROOT.'/log/*.log.php');

      if (is_array($logs) && count($logs)!=0) {
        foreach ($logs as $file) {
          $fileDate = basename(substr($file, 0, stripos($file, '.')));
          if ($fileDate <= $expireDate) {   //文件日期<过期日期时,删除日志文件
            unlink($file);
          }
        }
      }
    }
  }
}
?>