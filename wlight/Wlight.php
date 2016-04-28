<?php
/**
 * 框架基础入口文件
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

//版本信息
define('WLIGHT', 'Wlight');
define('WLIGHT_VERSION', '2.0');

//检查用户配置完整性
defined('APP_ID') or die('APP_ID miss !');
defined('APP_SECRET') or die('APP_SECRET miss !');
defined('APP_NAME') or die('APP_NAME miss !');
defined('WECHAT_ID') or die('WECHAT_ID miss !');
defined('TOKEN') or die('TOKEN miss !');
defined('DB_USER') or die('DB_USER miss !');
defined('DB_PWD') or die('DB_PWD miss !');

//常量默认值定义
defined('DEBUG_MODE') or define('DEBUG_MODE', false);                         //调试模式
defined('HOST') or define('HOST', "http://$_SERVER[HTTP_HOST]");              //主机URL
defined('PATH') or define('PATH', substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')));   //框架路径
defined('ENCODING_AESKEY') or define('ENCODING_AESKEY', '');                  //加密AES_KEY
defined('DB_TYPE') or define('DB_TYPE', 'mysql');                             //数据库类型
defined('DB_HOST') or define('DB_HOST', 'localhost');                         //数据库地址
defined('DB_PORT') or define('DB_PORT', '3306');                              //数据库监听端口
defined('DB_NAME') or define('DB_NAME', WECHAT_ID.'_wlight');                 //数据库名
defined('DB_PREFIX') or define('DB_PREFIX', 'wlight');                        //框架数据库表前缀
defined('DB_CHARSET') or define('DB_CHARSET', 'utf8');                        //数据库字符集
defined('DB_COLLATION') or define('DB_COLLATION', DB_CHARSET.'_general_ci');  //数据库排序规则
defined('RECORD_LIVE') or define('RECORD_LIVE', 40);                        //记录保存天数
defined('LOG_LIVE') or define('LOG_LIVE', 30);                              //日志保存天数
defined('MAX_CACHE') or define('MAX_CACHE', 300);                             //最大消息缓存数

//文件系统常量
define('DIR_ROOT', substr(str_replace("\\", "/", dirname(__FILE__)), 0, strrpos(str_replace("\\", "/", dirname(__FILE__)), '/')));
define('RUNTIME_ROOT', DIR_ROOT.'/runtime');
define('MSG_ROOT', DIR_ROOT.'/message');

//以下目录对应常量可供用户自定义
defined('APP_ROOT') or define('APP_ROOT', DIR_ROOT.'/application');
defined('RES_ROOT') or define('RES_ROOT', DIR_ROOT.'/resource');

//创建目录
wlight_makeDirectory(RUNTIME_ROOT);
wlight_makeDirectory(RUNTIME_ROOT.'/cache');
wlight_makeDirectory(RUNTIME_ROOT.'/log');
wlight_makeDirectory(RUNTIME_ROOT.'/log/info');
wlight_makeDirectory(RUNTIME_ROOT.'/log/error');
wlight_makeDirectory(RUNTIME_ROOT.'/lock');
wlight_makeDirectory(MSG_ROOT);
wlight_makeDirectory(MSG_ROOT.'/text');
wlight_makeDirectory(MSG_ROOT.'/image');
wlight_makeDirectory(MSG_ROOT.'/voice');
wlight_makeDirectory(MSG_ROOT.'/video');
wlight_makeDirectory(MSG_ROOT.'/shortvideo');
wlight_makeDirectory(MSG_ROOT.'/link');
wlight_makeDirectory(MSG_ROOT.'/location');
wlight_makeDirectory(MSG_ROOT.'/event');
wlight_makeDirectory(MSG_ROOT.'/event/subscribe');
wlight_makeDirectory(MSG_ROOT.'/event/unsubscribe');
wlight_makeDirectory(MSG_ROOT.'/event/CLICK');
wlight_makeDirectory(MSG_ROOT.'/event/SCAN');
wlight_makeDirectory(MSG_ROOT.'/event/LOCATION');
wlight_makeDirectory(MSG_ROOT.'/event/VIEW');
wlight_makeDirectory(APP_ROOT);
wlight_makeDirectory(RES_ROOT);

//文件锁
define('LOCK_CACHE', RUNTIME_ROOT.'/lock/cache.lock');
define('LOCK_ACCESS_TOKEN', RUNTIME_ROOT.'/lock/access_token.lock');
define('LOCK_JSAPI_TICKET', RUNTIME_ROOT.'/lock/jsapi_ticket.lock');
wlight_makeFile(LOCK_CACHE);
wlight_makeFile(LOCK_ACCESS_TOKEN);
wlight_makeFile(LOCK_JSAPI_TICKET);

function wlight_makeDirectory($dir) {
  if (!is_dir($dir)) {
    mkdir($dir);
    chmod($dir, 0777);
  }
}

function wlight_makeFile($file) {
  if (!file_exists($file)) {
    file_put_contents($file, "");
  }
}

//Request.php接收
require(DIR_ROOT.'/wlight/library/core/request/Request.php');

?>