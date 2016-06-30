# **配置** #

首次使用框架，需在入口文件定义配置常量，格式为：

    define('CONFIG', 'VALUE');

此处分为必要配置和可选配置。

## **必要配置** ##

必要配置表示必须在index.php入口文件中填写的配置常量，任一配置未定义则触发脚本错误。

- **APP_ID：** 公众平台appid
- **APP_SECRET：** 公众平台appsecret
- **APP_NAME：** 公众平台帐号名称
- **WECHAT_ID：** 公众平台微信号
- **TOKEN：** Token值
- **DB_USER：** 数据库用户名
- **DB_PWD：** 数据库密码

## **可选配置** ##

可选配置表示当用户未定义该配置时，使用框架预定义的值。

- **DEBUG：** 调试模式，true表示开启
- **HOST：** 主机URL
- **PATH：** 当前框架所在的路径
- **ENCODING_AESKEY：** 加密AES\_KEY值，**不填则表示使用明文模式**
- **DB_TYPE：** 数据库类型（**注意：当前版本仅支持mysql数据库**）
- **DB_HOST：** 数据库地址【默认值localhost】
- **DB_PORT：** 数据库监听端口【默认值3306】
- **DB_NAME：** 数据库名【默认值Wlight】
- **DB_PREFIX：** 框架相关的数据库表前缀【默认值wlight】
- **DB_CHARSET：** 数据库字符集【默认值utf8】
- **DB_COLLATION：** 数据库默认排序规则【默认值utf8\_general\_ci】
- **MEMCACHE_HOST：** Memcache服务地址【默认值localhost】
- **MEMCACHE_PORT：** Memcache服务端口【默认值11211】
- **MEMCACHE_ENABLE：** Memcache开关，若Memcache服务运行则默认为true。开发者可以自行配置强行关闭或开启（但不建议）
- **RECORD_LIVE：** 消息记录保存天数【默认值40】
- **LOG_LIVE：** 日志保存天数【默认值30】
- **MAX_CACHE：** 最大消息缓存数【默认值100】
- **APP_ROOT：** application目录路径【默认值 根目录/application】
- **RES_ROOT：** resource资源文件目录路径【默认值 根目录/resource】

## **其它配置** ##

除了以上配置可供用户定义外，还有部分配置不允许用户更改，但可在开发中使用。

- **DIR_ROOT：** Wlight框架的根目录所在位置（文件系统角度，非网络路径）
- **RUNTIME_ROOT：** runtime目录路径
- **MSG_ROOT：**message目录路径