# **配置** #

首次使用框架，需在入口文件定义配置常量，格式为：

    define('CONFIG', 'VALUE');

## **必要配置** ##

必要配置表示必须在index.php入口文件中填写的配置常量，任一配置未定义或未控制则报错。

- **APP_ID**
- **APP_SECRET**
- **APP_NAME**
- **WECHAT_ID**
- **TOKEN**
- **DB_USER**
- **DB_PWD**

## **配置列表** ##

### **平台配置** ###

- **APP\_ID：** 平台appid
- **APP\_SECRET：** 平台appsecret
- **APP\_NAME：** 应用名称
- **WECHAT\_ID：** 平台微信号
- **TOKEN：** token参数
- **ENCODING\_AESKEY：** 加密AES\_KEY，空表示使用明文模式

### **数据库配置** ###

- **DB\_USER：** 用户名
- **DB\_PWD：** 密码
- **DB\_TYPE：** 数据库类型，目前仅支持mysql
- **DB\_HOST：** 主机地址（可改）
- **DB\_PORT：** 端口（可改）
- **DB\_NAME：** 数据库名（可改）
- **DB\_PREFIX：** Wlight表前缀（可改，默认wlight）
- **DB\_CHARSET：** 字符集（可改，默认utf8）
- **DB\_COLLATION：** 排序规则（可改，默认utf8\_general\_ci）

### **文件系统配置** ###

- **DIR\_ROOT：** 根目录路径
- **APP\_ROOT：** application模块路径
- **MSG\_ROOT：** message模块路径
- **RES\_ROOT：** 资源目录路径
- **RUNTIME\_ROOT：** runtime目录路径

### **URL配置** ###

- **HOST：** URL域名（可改）
- **PATH：** URL路径（可改）
- **WLIGHT\_URL：** 根目录URL
- **APP\_URL：** application模块URL
- **RES\_URL：** 资源目录URL

### **运行时配置** ###
- **DEBUG：** 调试模式（可改，true表示开启，默认关闭）
- **DEFAULT\_TIMEZONE：** 时区（可改，默认PRC）
- **DEFAULT\_PERMISSION：** 文件权限（可改，默认775）
- **STATIS\_LIVE：** 统计记录保留天数（可改，默认40天）
- **LOG\_LIVE：** 日志保留天数（可改，默认30天）
- **MAX\_CACHE：** 最大缓存数（可改，默认100）