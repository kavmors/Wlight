<?php
/**
 * 处理Api请求过程出错的Exception处理类
 * @author  KavMors(kavmors@163.com)
 *
 * __construct(string, int string)
 * void log()
 * void printInfo()
 * string getInfo()
 */

namespace wlight\runtime;

class ApiException extends \Exception {
  //框架定义状态码
  const HTTP_ERROR_CODE = -101;
  const JSON_DECODE_ERROR_CODE = -102;
  const ERROR_JSON_ERROR_CODE = -103;
  const FILE_NOT_EXISTS_ERROR_CODE = -104;
  const OAUTH_REJECT_ERROR_CODE = -105;
  const FILE_LOCK_ERROR_CODE = -106;
  const UNKNOW_ERROR_CODE = -199;

  const HTTP_ERROR_MSG = 'failed in http request';
  const JSON_DECODE_ERROR_MSG = 'not a string in json format';
  const ERROR_JSON_ERROR_MSG = 'illegal array decoded by json';
  const FILE_NOT_EXISTS_ERROR_MSG = 'file not exists in media uploading';
  const OAUTH_REJECT_ERROR_MSG = 'authentication reject by user';
  const FILE_LOCK_ERROR_MSG = 'fail to access token file';
  const UNKNOW_ERROR_MSG = 'unknow exception';

  private $extraInfo;

  /**
   * 判断某个错误码是否属于ApiException的错误
   * @param int $code 错误码
   * @return boolean
   */
  public static function includeCode($code) {
    return $code < -100 && $code < -200;
  }

  public function __construct($message, $code, $extraInfo='') {
    parent::__construct($message, $code);
    $this->extraInfo = $extraInfo;
  }

  /**
   * 记录Exception信息到日志
   * 日志仅用于保存Wlight自动回复任务导致的ApiException信息
   */
  public function log() {
    include_once (DIR_ROOT.'/wlight/library/runtime/Log.class.php');
    if (!class_exists('\wlight\runtime\Log')) {
      return;
    }
    if ($this->extraInfo == '') {
      Log::e($this);
    } else {
      Log::e($this, array('extra' => $this->extraInfo));
    }
  }

  /**
   * 输出Exception信息
   *
   * 此函数用于调试,方便分析ApiException错误信息.
   * 请勿在生产环境使用此函数.
   */
  public function printInfo() {
    echo $this->getInfo();
  }

  /**
   * 获取Exception信息
   */
  public function getInfo() {
    $type = 'ApiException';
    $file = $this->getFile();
    $code = $this->getCode();
    $line = $this->getLine();
    $msg = $this->getMessage();
    $trace = $this->getTraceAsString();

    $message = "  [Code]\t$code\n";
    $message .= "  [Message]\t$msg\n";
    $message .= "  [At]\t$file(Line $line)\n";
    $message .= $trace;
    $content = "[$type]------------------------------\n$message";
    return $content;
  }

  public static function throws($code, $extraInfo='') {
    switch ($code) {
      case self::HTTP_ERROR_CODE: return new ApiException(self::HTTP_ERROR_MSG, self::HTTP_ERROR_CODE, $extraInfo);
      case self::JSON_DECODE_ERROR_CODE: return new ApiException(self::JSON_DECODE_ERROR_MSG, self::JSON_DECODE_ERROR_CODE, $extraInfo);
      case self::ERROR_JSON_ERROR_CODE: return new ApiException(self::ERROR_JSON_ERROR_MSG, self::ERROR_JSON_ERROR_CODE, $extraInfo);
      case self::FILE_NOT_EXISTS_ERROR_CODE: return new ApiException(self::FILE_NOT_EXISTS_ERROR_MSG, self::FILE_NOT_EXISTS_ERROR_CODE, $extraInfo);
      case self::OAUTH_REJECT_ERROR_CODE: return new ApiException(self::OAUTH_REJECT_ERROR_MSG, self::OAUTH_REJECT_ERROR_CODE, $extraInfo);
      case self::FILE_LOCK_ERROR_CODE: return new ApiException(self::FILE_LOCK_ERROR_MSG, self::FILE_LOCK_ERROR_CODE, $extraInfo);
      default: return new ApiException(self::UNKNOW_ERROR_MSG, self::UNKNOW_ERROR_CODE, $extraInfo);
    }
  }
}

?>