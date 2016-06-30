# **框架机制** #

Wlight 2.0采用新的架构重构代码，实现接口逻辑与开发逻辑分离、自动回复缓存等机制。开发者在了解框架机制后，可根据需求设置不同的功能。

## **接口逻辑与开发逻辑分离** ##

Wlight根据MsgType定位到/message/下的子目录，并遍历子目录下的所有php文件，进行升序排列后逐个导入，创建对应的类并执行verify。当执行到verify为true时返回该类invoke的结果，并结束遍历，完全遍历后仍没有匹配符合则返回emptyResponse。

核心代码位于/wlight/library/core/request/Controller.php中的ergodicPhps方法

	//循环遍历中执行
	...导入/message子目录文件操作

	//此处$classPath为根据文件名解析出的类名
	$key = new $classPath;
	//检测$key父类类型
	if (!is_subclass_of($key, '\wlight\core\Response')) {
	  continue;
	}
	//赋值传递参数
	$key->assign($this->postClass);
	//验证是否执行
	if ($key->verify()) {
	  //执行用户定义逻辑, 返回结果
	  $result = $key->invoke();

	  ...统计及缓存

	  $key = null;
	  return $result;
	}
	return false;

在确定该文件中的类为Response子类后，将消息体结构参数赋值到该类中（即赋值$this->map消息体数组），然后执行用户覆盖的verify。若verify返回true，则执行invoke，以及统计和缓存。

> 执行verify是遍历所有php文件执行的。启用缓存和更改/message/子目录下的文件名称（添加前缀）能使框架率先遍历某一文件，避免无效的匹配，以此来提升执行效率

实际开发中，开发者并不需要关心Wlight内部执行逻辑，只需覆盖Response中4个方法，加入自己的业务逻辑即可。

## **缓存** ##

缓存为2.0版本中新增的机制。由于遍历影响效率，在遍历一次并匹配成功的情况下，可将接收的消息和对应的文件缓存起来，下一次接收到相同消息时率先遍历该文件。在此情况下，一次匹配就成功的机率很大。当前版本仅对Text类型消息和CLICK类型事件进行缓存。

> 缓存使用LRU算法，有最大缓存记录数的限制，可通过配置MAX_CACHE修改，默认为300

若继承Response过程中不覆盖cache则默认加入缓存。相关核心代码位于/wlight/library/core/request/Controller.php中的putToCache、getFromCache、updateCacheFile方法。缓存文件路径为/runtime/cache/msg\_text.json.php及/runtime/cache/msg\_click.json.php。

> 如首次接收到"Hello"，通过遍历匹配到"HelloText.php"，则下一次接收到"Hello"会率先匹配"HelloText.php"，若执行verify返回false才重新遍历所有php文件。

> 并非所有消息都适合缓存。如verify中通过正则表达式"/[0-9]{6}/"匹配的，该正则表达式表示匹配任意6位数字。如加入缓存，可能因最大记录限制导致其他消息的缓存被丢弃，而相同的6位数字重现率并不高。这样会导致缓存不断刷新，降低缓存效果。

## **功能统计** ##

/message/子目录下的一个php文件包含一个类，一个类为一个自动回复功能。当接收到的消息触发这个类的invoke方法，则视为这个功能被使用了一次。通过功能统计，可记录每种自动回复功能每天被使用的次数。

> 功能统计记录默认保存40天，可通过配置RECORD_LIVE修改

> 在继承Response过程中，覆盖tag方法并返回一个字符串即可将该回复功能加入统计。返回null（或不覆盖）表示这个功能不需加入统计

核心代码位于/wlight/library/core/request/Controller.php中的callStatistics：

	private function callStatistics($className, $tag)

其中，$className为类名，作该功能的标识，$tag为用户覆盖tag方法返回的字符串。

在数据库中，涉及统计功能的表为wlight\_tag和wlight\_tag\_map（wlight为表前缀，可通过DB_PREFIX修改）。wlight\_tag首字段"date"为日期，其余字段为各个功能的标识（加入新功能修改表结构，添加一列）。wlight\_tag\_map包含字段"key"和"map"，代表功能标识和该功能的标签(tag)。

> 数据库操作相关代码位于/wlight/library/statistics/Tag.class.php

## **日志** ##

对于每次自动回复，Wlight都会将相关信息打印到日志文件（/wlight/runtime/log/info)；而对于出现错误的情况（数据库连接错误、ApiException日志输出），则会打印到错误日志（/wlight/runtime/log/error）。日志文件以日期作文件名，每个文件记录当天的日志信息。开发者可根据需要清理日志。

> 日志文件默认保存30天，超过30天的文件Wlight会自动清理。可通过配置LOG_LIVE修改
