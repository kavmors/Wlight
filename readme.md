# **Wlight 3.0** #

Wlight是一款面向业务逻辑开发的微信公众平台开发框架，包含对大部分基础API接口的封装。


# **框架特性** #

> - 接口逻辑与开发逻辑的分离
> - API接口封装
> - 数据统计功能
> - 缓存机制
> - 排重机制
> - 全局Hook机制

# **3.x版本更新内容** #

> - 优化Log输出
> - 新增留言表
> - 修改部分配置名与数据库表名

# **Quick start** #

## **2.x升级指引** ##

3.x版本兼容2.x版本构建的项目，但部分配置与数据库表名有变化，同时，框架各模块路径不可更改。

数据库表名变化：

- wlight\_tag -> wlight\_statis\_tag
- wlight\_cache -> wlight\_cache\_msg
- wlight\_statis\_message(新增)
- wlight\_cache\_retry(新增)
- wlight\_cache\_oauth(新增)
- wlight\_tag\_map(删除)

配置常量变化：

- RECORD\_LIVE -> STATIS\_LIVE
- WLIGHT\_URL(新增)
- APP\_URL(新增)
- RES\_URL(新增)
- DEFAULT\_PERMISSION(新增)
- LOCK\_CACHE(删除)
- LOCK\_ACCESS_TOKEN(删除)
- LOCK\_JSAPI_TICKET(删除)

**3.0起不再支持Memcache，全部改用数据库缓存，如有需要请自行开发。**

**3.0各版本更新内容请参考[Wiki](https://github.com/kavmors/Wlight/wiki/Whatsnew)。**

## **权限** ##

部署前，请保证以下权限：

1. 服务对本项目所有文件夹及子文件有读写权限（默认文件权限为775，可修改DEFAULT_PERMISSION以更改权限）。

2. 当前使用的数据库用户对指定的数据库有创表、删表、查询和更改表结构，以及对所有表增删改查的权限。若无创数据库权限，请自行建库。

## **部署** ##

在根目录index.php中填写相应的配置后，在公众平台配置页中设置URL指向index.php，保存后则完成配置。若配置失败，请查询日志。

完成公众平台部署后，在/message/text下新建自动回复规则。以下是框架的Sample.php示例文件。

	<?php

	/**
	 * Sample file
	 */
	namespace wlight\msg;
	use wlight\core\Response;

	//类名与文件名的主体部分相同
	//详细规则参考wiki
	class Sample extends Response {
	  public function verify() {
	    //返回true时执行invoke方法
	    return $this->map['Content'] == 'hello';
	  }

	  public function invoke() {
	    //回复内容
	    return $this->makeText('you sent: '. $this->map['Content']);
	  }

	  public function cache() {
	    return true;    //缓存控制
	  }

	  public function tag() {
	    return '示例';  //数据统计标签
	  }
	}

	?>

详细文档请参考[Wiki](https://github.com/kavmors/Wlight/wiki)。


# **Author** #

KavMors (kavmors@163.com)


# **License** #

	Copyright 2016 KavMors

	Licensed under the Apache License, Version 2.0 (the "License");
	you may not use this file except in compliance with the License.
	You may obtain a copy of the License at

		http://www.apache.org/licenses/LICENSE-2.0

	Unless required by applicable law or agreed to in writing, software
	distributed under the License is distributed on an "AS IS" BASIS,
	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	See the License for the specific language governing permissions and
	limitations under the License.