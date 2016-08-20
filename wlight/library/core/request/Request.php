<?php
/**
 * 接收微信服务器的请求
 * @author  KavMors(kavmors@163.com)
 */

namespace wlight\core;
use wlight\runtime\Log;

if (!DEBUG) {
  ob_start();     //开启缓冲
}

include (DIR_ROOT.'/wlight/library/runtime/Log.class.php');
include (DIR_ROOT.'/wlight/library/util/DbHelper.class.php');
include (DIR_ROOT.'/wlight/library/core/support/DbDeployer.class.php');

$currentDir = DIR_ROOT.'/wlight/library/core/request';
Log::start();

if (isset($_GET['echostr'])) {
  Log::verify();

  $echoStr = $_GET['echostr'];
  $dbResult = false;
  if (checkSignature()) {
    switch (DB_TYPE) {
      case 'mysql':
        $dbResult = support\DbDeployer::initMysql();
        break;
      /** 其他类型数据库暂不支持
      case 'others'
      **/
      default:
        $dbResult = support\DbDeployer::initMysql();
        break;
    }
    if ($dbResult) {
    	resetCache();
    }

    if (!DEBUG) {
    	Log::w(ob_get_contents());
      ob_end_clean();     //清理缓冲
    }
    echo $dbResult ? $echoStr : '';
  }
} elseif ($_SERVER['REQUEST_METHOD']=='POST') {
  Log::reply();
  include ($currentDir.'/Controller.php');

  $postRaw = file_get_contents("php://input");
  if (ENCODING_AESKEY != '') {        //加密情况下
    include ($currentDir.'/../encrypt/Encryptor.class.php');
    $encryptor = new encrypt\Encryptor(TOKEN, ENCODING_AESKEY, APP_ID);

    //解密
    $errorCode = $encryptor->decrypt($_GET['msg_signature'], $_GET['timestamp'], $_GET['nonce'], $postRaw, $postRaw);
    checkErrorCode($errorCode);

    //执行逻辑
    $controller = new Controller($postRaw);
    $response = $controller->action();

    //加密
    $errorCode = $encryptor->encrypt($response, $_GET['timestamp'], $_GET['nonce'], $response);
    checkErrorCode($errorCode);

    //结果返回
    if (!DEBUG) {
      Log::w(ob_get_contents());
      ob_end_clean();     //清理缓冲
    }
    echo $response;
  } else {
    //执行逻辑
    $controller = new Controller($postRaw);
    $response = $controller->action();

    //结果返回
    if (!DEBUG) {
      Log::w(ob_get_contents());
      ob_end_clean();     //清理缓冲
    }
    echo $response;
  }
} elseif (DEBUG===true) {
  Log::cancel();

	$dbResult = false;
  switch (DB_TYPE) {
    case 'mysql':
      $dbResult = support\DbDeployer::initMysql();
      break;
    /** 其他类型数据库暂不支持
    case 'others'
    **/
    default:
      $dbResult = support\DbDeployer::initMysql();
      break;
  }
  if ($dbResult) {
  	resetCache();
  }
}

//全脚本结束,日志写入文件
Log::end();

function checkSignature() {
  $signature = $_GET["signature"];
  $timestamp = $_GET["timestamp"];
  $nonce = $_GET["nonce"];

  $token = TOKEN;
  $tmpArr = array($token, $timestamp, $nonce);
  sort($tmpArr, SORT_STRING);
  $tmpStr = implode($tmpArr);
  $tmpStr = sha1($tmpStr);

  return $tmpStr == $signature;
}

function checkErrorCode($errorCode) {
  if ($errorCode != 0) {
    die("Failed in decrypt(errorCode:$errorCode)");
  }
}

//重置缓存: 清理缓存,清理Token
function resetCache() {
  @unlink(RUNTIME_ROOT.'/cache/access_token.json.php');
  @unlink(RUNTIME_ROOT.'/cache/jsapi_ticket.json.php');
  @unlink(RUNTIME_ROOT.'/cache/config.json.php');

  support\DbDeployer::resetMysql();
  include_once (DIR_ROOT.'/wlight/library/core/support/Deployer.class.php');
  support\Deployer::initConfig();
}
?>