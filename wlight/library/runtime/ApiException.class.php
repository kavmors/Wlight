<?php
/**
 * 处理Api请求过程出错的Exception处理类
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\runtime;
use wlight\core\support\Locker;

class ApiException extends \Exception {
  //框架定义状态码
  const HTTP_ERROR_CODE = -101;
  const JSON_DECODE_ERROR_CODE = -102;
  const ERROR_JSON_ERROR_CODE = -103;
  const FILE_NOT_EXISTS_ERROR_CODE = -104;
  const OAUTH_REJECT_ERROR_CODE = -105;

  const HTTP_ERROR_MSG = 'failed in http request';
  const JSON_DECODE_ERROR_MSG = 'not a string in json format';
  const ERROR_JSON_ERROR_MSG = 'illegal array decoded by json';
  const FILE_NOT_EXISTS_ERROR_MSG = 'file not exists in media uploading';
  const OAUTH_REJECT_ERROR_MSG = 'authentication reject by user';

  private $extraInfo;

  public function __construct($message, $code, $extraInfo='') {
    parent::__construct($message, $code);
    $this->extraInfo = $extraInfo;

    include_once (DIR_ROOT.'/wlight/library/core/support/Locker.class.php');
    $this->unlockAll();
  }

  /**
   * 记录Exception信息到日志
   */
  public function log() {
    if (!class_exists('\wlight\runtime\Log')) {
      return;
    }
    $log = Log::getInstance();
    $log->e('-----ApiException-----');
    $log->e('errcode', $this->getCode());
    $log->e('errMessage', $this->getMessage());
    $log->e('file', $this->getFile());
    $log->e('line', $this->getLine());
    $log->e('traceAsString', $this->getTraceAsString());
    $log->e('extraInfo', $this->extraInfo);
    $log->e('-----/ApiException-----');
  }

  /**
   * 输出Exception信息
   */
  public function printInfo() {
    echo $this->getInfo();
  }

  /**
   * 获取Exception信息
   */
  public function getInfo() {
    $msg = '';
    $msg .= '[ -----ApiException----- ]'."\n";
    $msg .= '[ errcode ]'. " ". $this->getCode()."\n";
    $msg .= '[ errMessage ]'. " ". $this->getMessage()."\n";
    $msg .= '[ file ]'. " ". $this->getFile()."\n";
    $msg .= '[ line ]'. " ". $this->getLine()."\n";
    $msg .= '[ traceAsString ]'. " ". $this->getTraceAsString()."\n";
    $msg .= '[ extraInfo ]'. " ". $this->extraInfo."\n";
    $msg .= '[ -----/ApiException----- ]'."\n";
    return $msg;
  }

  private function unlockAll() {
    Locker::getInstance(LOCK_CACHE)->unlock();
    Locker::getInstance(LOCK_ACCESS_TOKEN)->unlock();
    Locker::getInstance(LOCK_JSAPI_TICKET)->unlock();
  }

  public static function httpException($extraInfo='') {
    throw new ApiException(self::HTTP_ERROR_MSG, self::HTTP_ERROR_CODE, $extraInfo);
  }

  public static function jsonDecodeException($extraInfo='') {
    return new ApiException(self::JSON_DECODE_ERROR_MSG, self::JSON_DECODE_ERROR_CODE, $extraInfo);
  }

  public static function errorJsonException($extraInfo='') {
    return new ApiException(self::ERROR_JSON_ERROR_MSG, self::ERROR_JSON_ERROR_CODE, $extraInfo);
  }

  public static function fileNotExistsException($extraInfo='') {
    return new ApiException(self::FILE_NOT_EXISTS_ERROR_MSG, self::FILE_NOT_EXISTS_ERROR_CODE, $extraInfo);
  }
}

?>