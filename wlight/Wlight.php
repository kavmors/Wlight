<?php
/**
 * 框架基础入口文件
 * @author  KavMors(kavmors@163.com)
 * @version 3.0
 */

//版本信息
define('WLIGHT', 'WLIGHT');
define('WLIGHT_VERSION', '3.0');

//平台配置
defined('APP_ID') && APP_ID != '' or die('APP_ID miss');                      //appid
defined('APP_SECRET') && APP_SECRET != '' or die('APP_SECRET miss');          //appsecret
defined('APP_NAME') && APP_NAME != '' or die('APP_NAME miss');                //应用名称
defined('WECHAT_ID') && WECHAT_ID != '' or die('WECHAT_ID miss');             //平台微信号
defined('TOKEN') && TOKEN != '' or die('TOKEN miss');                         //token
defined('ENCODING_AESKEY') or define('ENCODING_AESKEY', '');                  //加密AES_KEY

//数据库配置
defined('DB_USER') && DB_USER != '' or die('DB_USER miss');                   //数据库用户名
defined('DB_PWD') && DB_PWD != '' or die('DB_PWD miss');                      //数据库密码
defined('DB_TYPE') or define('DB_TYPE', 'mysql');                             //数据库类型
defined('DB_HOST') or define('DB_HOST', 'localhost');                         //地址
defined('DB_PORT') or define('DB_PORT', '3306');                              //监听端口
defined('DB_NAME') or define('DB_NAME', WECHAT_ID.'_wlight');                 //数据库名
defined('DB_PREFIX') or define('DB_PREFIX', 'wlight');                        //框架数据库表前缀
defined('DB_CHARSET') or define('DB_CHARSET', 'utf8');                        //字符集
defined('DB_COLLATION') or define('DB_COLLATION', DB_CHARSET.'_general_ci');  //排序规则

//File System
define('DIR_ROOT', substr(str_replace("\\", "/", dirname(__FILE__)), 0, strrpos(str_replace("\\", "/", dirname(__FILE__)), '/')));
define('APP_ROOT', DIR_ROOT.'/application');                                  //Application module路径
define('RES_ROOT', DIR_ROOT.'/resource');                                     //Resource module路径
define('MSG_ROOT', DIR_ROOT.'/message');                                      //Message module路径
define('RUNTIME_ROOT', DIR_ROOT.'/runtime');                                  //runtime路径

//URL
defined('HOST') or define('HOST', "http://$_SERVER[HTTP_HOST]");              //主机URL
defined('PATH') or define('PATH', substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')));   //框架路径
define('WLIGHT_URL', HOST.PATH);                                              //根目录url
define('APP_URL', WLIGHT_URL.'/application');                                 //Application module url
define('RES_URL', WLIGHT_URL.'/resource');                                    //Resource module url

//运行时配置
defined('DEBUG') or define('DEBUG', false);                                   //调试模式
defined('DEFAULT_TIMEZONE') or define('DEFAULT_TIMEZONE', 'PRC');             //默认时区
defined('DEFAULT_PERMISSION') or define('DEFAULT_PERMISSION', 0775);          //默认文件权限
defined('STATIS_LIVE') or define('STATIS_LIVE', 40);                          //统计记录保存天数
defined('LOG_LIVE') or define('LOG_LIVE', 30);                                //日志保存天数
defined('MAX_CACHE') or define('MAX_CACHE', 100);                             //最大消息缓存数

//执行配置
date_default_timezone_set(DEFAULT_TIMEZONE);

//初始化部署
if (file_exists(DIR_ROOT.'/Sample.php') || file_exists(DIR_ROOT.'/Hook.php')) {
  include (DIR_ROOT.'/wlight/library/core/support/Deployer.class.php');
  wlight\core\support\Deployer::initFileSystem();
}

//Request.php接收
require (DIR_ROOT.'/wlight/library/core/request/Request.php');
?>