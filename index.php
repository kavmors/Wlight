<?php
/**
 * 应用入口文件
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

//定义平台变量
define('APP_ID', '');           //应用ID
define('APP_SECRET', '');       //应用密匙
define('APP_NAME', '');         //应用名称
define('WECHAT_ID', '');        //公众平台微信号
define('TOKEN', '');            //令牌
define('ENCODING_AESKEY', '');  //加密所用的AES_KEY
define('DB_USER', '');          //数据库用户
define('DB_PWD', '');           //数据库密码

//引入框架入口文件
require './wlight/Wlight.php';

//配置微信平台“服务器配置”后修改Application里的文件