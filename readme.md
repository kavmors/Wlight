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

# **Quick start** #

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
