# **Wlight 2.3** #

Wlight是一款面向开发的微信公众平台开发框架，包含对大部分基础API接口的封装。2.x版本在原来类库的基础上进行了架构调整，引入命名空间，使逻辑更加适合于开发。


# **2.x 框架特性** #

> - 引入命名空间，完善模块化，实现接口逻辑与开发逻辑的分离
> - 根据接口功能，将各个API类库进行分类
> - 重写部分接口类库，规范化接口
> - 重写数据统计功能
> - 添加缓存机制

# **2.1 更新** #

> - 更改类库引入方式
> - 简化配置导入流程

# **2.2 更新** #

> - 优化初始化部署的逻辑
> - 新增全局Hook机制
> - 新增部署后示例文件，方便快速搭建

# **2.3 更新** #

> - 增加微信超时重发的排重机制
> - 排重机制支持数据库和Memcache，支持自动切换

# **运行环境要求** #

- 操作系统：Windows/Unix服务器环境
- 服务器：Apache、Nginx等支持PHP的服务器软件
- PHP：5.3以上版本
- 数据库：目前仅支持MySQL(5.*)