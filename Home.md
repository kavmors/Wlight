# **Wlight 3.0** #

Wlight是一款面向开发的微信公众平台开发框架，包含对大部分基础API接口的封装。3.x版本在2.x的基础上优化了API类库，完善统计功能，提高框架的执行效率。

# **框架特性** #

> - 接口逻辑与开发逻辑的分离
> - API接口封装
> - 数据统计功能
> - 缓存机制
> - 排重机制
> - 全局Hook机制

# **运行环境要求** #

- 操作系统：Windows/Linux服务器环境
- 服务器：Apache、Nginx等支持PHP的服务器软件
- PHP：5.3以上版本
- 数据库：目前仅支持MySQL(5.*)


----------------------------------

# 文档目录

- [文件目录结构](./Directory)
- [配置](./Config)
- &emsp;[必要配置](./Config#必要配置)
- &emsp;[配置列表](./Config#配置列表)
- &emsp;&emsp;[平台配置](./Config#平台配置)
- &emsp;&emsp;[数据库配置](./Config#数据库配置)
- &emsp;&emsp;[文件系统配置](./Config#文件系统配置)
- &emsp;&emsp;[URL配置](./Config#url配置)
- &emsp;&emsp;[运行时配置](./Config#运行时配置)
- [开发](./develop)
- &emsp;[平台部署](./develop#平台配置)
- &emsp;[自动回复开发](./develop#自动回复开发)
- &emsp;&emsp;[verify](./develop#verify)
- &emsp;&emsp;[invoke](./develop#invoke)
- &emsp;&emsp;[tag](./develop#tag)
- &emsp;&emsp;[cache](./develop#cache)
- &emsp;&emsp;[引入API类库](./develop#引入api类库)
- &emsp;&emsp;[消息体结构参数](./develop#消息体结构参数)
- &emsp;&emsp;[文件名与类名规则](./develop#文件名与类名规则)
- &emsp;&emsp;[全局Hook机制](./develop#全局hook机制)
- [框架机制](./core)
- &emsp;[接口逻辑与开发逻辑分离](./core#接口逻辑与开发逻辑分离)
- &emsp;[缓存](./core#缓存)
- &emsp;[统计](./core#统计)
- &emsp;&emsp;[功能统计](./core#功能统计)
- &emsp;&emsp;[消息统计](./core#消息统计)
- &emsp;[日志](./core#日志)
- [API类库](./API)
- &emsp;[命名空间与分类](./API#命名空间与分类)
- &emsp;[导入API类](./API#导入api类)
- &emsp;[异常处理](./API#异常处理)
