# **文件目录结构** #

初始目录只有index.php和wlight，配置微信平台后目录结构如下：

- **index.php：** 入口文件
- **wlight：** 框架库文件目录
- **application：** 外置开发应用目录
- **message：** 响应自动回复开发目录
- **resource：** 资源文件目录
- **runtime：** 运行时目录
> 其中application、resource目录可由开发者自行定义，请参考[配置](./Config)

> runtime目录包含自动回复access\_token、jsapi\_ticket、缓存、配置记录及日志，开发者可以按需要清理目录下的文件