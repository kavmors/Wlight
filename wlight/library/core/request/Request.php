<?php
/**
 * 接收微信服务器的请求
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\core;
use wlight\runtime\Log;

include (DIR_ROOT.'/wlight/library/runtime/Log.class.php');

Log::getInstance()->start();

$currentDir = DIR_ROOT.'/wlight/library/core/request';

if (isset($_GET['echostr'])) {
  Log::getInstance()->i('action', 'verify');
  $echoStr = $_GET['echostr'];
  if (checkSignature()) {
    switch (DB_TYPE) {
      case 'mysql':
        require($currentDir.'/Mysql.php');
        break;
      /** 其他类型数据库暂不支持
      case 'others'
      **/
      default:
        require($currentDir.'/Mysql.php');
        break;
    }
    markConfig();
    echo $echoStr;
  }
} elseif ($_SERVER['REQUEST_METHOD']=='POST') {
  Log::getInstance()->i('action', 'reply');
  include ($currentDir.'/Controller.php');

  $postRaw = file_get_contents("php://input");
  if (!empty(ENCODING_AESKEY)) {        //加密情况下
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
    echo $response;
  } else {
    //执行逻辑
    $controller = new Controller($postRaw);
    $response = $controller->action();

    //结果返回
    echo $response;
  }

  //若配置文件不存在, 重新写入配置
  if (!file_exists(RUNTIME_ROOT.'/cache/config.json.php')) {
    markConfig();
  }
} elseif (DEBUG_MODE===true) {
  Log::getInstance()->i('action', 'debug');

  switch (DB_TYPE) {
    case 'mysql':
      require($currentDir.'/Mysql.php');
      break;
    /** 其他类型数据库暂不支持
    case 'others'
    **/
    default:
      require($currentDir.'/Mysql.php');
      break;
  }
  markConfig();
}

//全脚本结束,日志写入文件
Log::getInstance()->end();

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
  if ($errorCode!=0) {
    die("Failed in decrypt(errorCode:$errorCode)");
  }
}

//记录配置项
function markConfig() {
  Log::getInstance()->i('mark-config');

  $config = array(
    'HOST' => HOST,
    'PATH' => PATH,
    'DB_TYPE' => DB_TYPE,
    'DB_HOST' => DB_HOST,
    'DB_PORT' => DB_PORT,
    'DB_NAME' => DB_NAME,
    'DB_USER' => DB_USER,
    'DB_PWD' => DB_PWD,
    'DB_PREFIX' => DB_PREFIX,
    'DB_CHARSET' => DB_CHARSET,
    'DB_COLLATION' => DB_COLLATION,
    'RECORD_LIVE' => RECORD_LIVE,
    'LOG_LIVE' => LOG_LIVE,
    'MAX_CACHE' => MAX_CACHE,
    'APP_ID' => APP_ID,
    'APP_SECRET' => APP_SECRET,
    'APP_NAME' => APP_NAME,
    'WECHAT_ID' => WECHAT_ID,
    'TOKEN' => TOKEN,
    'ENCODING_AESKEY' => ENCODING_AESKEY,
    'APP_ROOT' => APP_ROOT,
    'DIR_ROOT' => DIR_ROOT,
    'MSG_ROOT' => MSG_ROOT,
    'RUNTIME_ROOT' => RUNTIME_ROOT,
    'RES_ROOT' => RES_ROOT,
    'LOCK_CACHE' => LOCK_CACHE,
    'LOCK_ACCESS_TOKEN' => LOCK_ACCESS_TOKEN,
    'LOCK_JSAPI_TICKET' => LOCK_JSAPI_TICKET
  );
  //写入配置时用php形式储存, 防止被查看
  $configStr = '<?php exit; ?>'.json_encode($config);
  file_put_contents(RUNTIME_ROOT.'/cache/config.json.php', $configStr);
  chmod(RUNTIME_ROOT.'/cache/config.json.php', 0777);
}
?>