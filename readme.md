# **Wlight 2.0** #

Wlight是一款面向开发的微信公众平台开发框架，包含对大部分基础API接口的封装。2.0版本在原来类库的基础上进行了架构调整，引入命名空间，使逻辑更加适合于开发。


# **2.x 框架特性** #

> - 引入命名空间，完善模块化，实现接口逻辑与开发逻辑的分离
> - 根据接口功能，将各个API类库进行分类
> - 重写部分接口类库，规范化接口
> - 重写数据统计功能
> - 添加缓存机制

# **2.1 更新** #

> - 更改类库引入方式
> - 简化配置导入流程

# **运行环境要求** #

- 操作系统：Windows/Unix服务器环境
- 服务器：Apache、Nginx等支持PHP的服务器软件
- PHP：5.3以上版本
- 数据库：目前仅支持MySQL(5.*)

----------------------------------

# 文档目录

- [文件目录结构](#文件目录结构)
- [配置](#配置)
- &emsp;[必要配置](#必要配置)
- &emsp;[可选配置](#可选配置)
- &emsp;[其它配置](#其它配置)
- [开发](#开发)
- &emsp;[平台配置](#平台配置)
- &emsp;[自动回复开发](#自动回复开发)
- &emsp;&emsp;[verify](#verify)
- &emsp;&emsp;[invoke](#invoke)
- &emsp;&emsp;[tag](#tag)
- &emsp;&emsp;[cache](#cache)
- &emsp;&emsp;[引入API类库](#引入api类库)
- &emsp;&emsp;[消息体结构参数](#消息体结构参数)
- &emsp;&emsp;[文件名与类名规则](#文件名与类名规则)
- [框架机制](#框架机制)
- &emsp;[接口逻辑与开发逻辑分离](#接口逻辑与开发逻辑分离)
- &emsp;[缓存](#缓存)
- &emsp;[功能统计](#功能统计)
- &emsp;[日志](#日志)
- [API类库](#api类库)
- &emsp;[命名空间与分类](#命名空间与分类)
- &emsp;[导入API类](#导入api类)
- &emsp;[异常处理](#异常处理)
- [附录1：API类库接口规范](#附录1api类库接口规范)

--------------------------------


# **文件目录结构** #

初始目录只有index.php和wlight，配置微信平台后目录结构如下：

- **index.php：** 入口文件
- **wlight：** 框架库文件目录
- **application：** 外置开发应用目录
- **message：** 响应自动回复开发目录
- **resource：** 资源文件目录
- **runtime：** 运行时目录
> 其中application、resource目录可由开发者自行定义，请参考[配置](#配置)

> runtime目录包含自动回复access\_token、jsapi\_ticket、缓存、配置记录及日志，开发者可以按需要清理目录下的文件


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

- **DEBUG_MODE：** 调试模式，true表示开启
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
- **RECORD_LIVE：** 消息记录保存天数【默认值40】
- **LOG_LIVE：** 日志保存天数【默认值30】
- **MAX_CACHE：** 最大消息缓存数【默认值300】
- **APP_ROOT：** application目录路径【默认值 根目录/application】
- **RES_ROOT：** resource资源文件目录路径【默认值 根目录/resource】

## **其它配置** ##

除了以上配置可供用户定义外，还有部分配置不允许用户更改，但可在开发中使用。

- **DIR_ROOT：** Wlight框架的根目录所在位置（文件系统角度，非网络路径）
- **RUNTIME_ROOT：** runtime目录路径
- **MSG_ROOT：**message目录路径


# **开发** #

修改框架基本配置信息后，即可接入公众平台的开发功能，包括针对公众平台网站的配置、基本自动回复功能。

## **平台配置** ##

在公众平台开发配置中，将对应的配置值填写并提交。其中，URL对应本框架根目录中index.php。提交成功后，框架所有目录及数据库自动生成。注意数据库用户需有新建数据库和创建数据库表的权限，若无权限，请手动创建数据库并赋予用户对该表的所有权限。

在调试模式下（DEBUG\_MODE=true），开发者可直接访问index.php检验目录及数据库是否生成，以及排查所有报错。在生产环境下建议将调试模式关闭。

> index.php文件仅为接收微信平台消息的入口，出于安全考虑，可更改此文件名称，更改后只需在公众平台网站配置中将URL修改为对应的路径即可。

## **自动回复开发** ##

成功接入并启用开发者功能后，可根据业务需求开发自动回复功能。此处涉及根目录下的/message文件夹。文件夹内子目录名称代表根据接收消息的MsgType分类（如/message/text/表示当用户发送的消息为Text文本时执行这个子目录下的脚本，/message/event/CLICK/则为当用户点击自定义菜单时执行脚本的目录）。根据接收消息的MsgType，在对应的子目录下新建php脚本，创建继承自Response类的子类及覆盖相关方法即可。一个php脚本文件对应一个回复功能。

以下例子展示接收用户消息并在该消息前添加"hello "后返回给用户：

	<?php
	  /**HelloText.php**/
	  namespace wlight\msg;
	  use wlight\core\Response;

	  class HelloText extends Response {
		public function verify() {
		  return true;
		}

		public function invoke() {
		  return $this->makeText('hello ' + $this->map['Content']);
		}
	
		public function tag() {
		  return '测试';
		}

		public function cache() {
		  return true;
		}
	  }
	?>

以上代码保存为HelloText.php，当接收到用户任何消息后，将在消息前加"hello "后发送回用户作回复。以下为各个覆盖方法的解释。

> 可按排序需求自定义文件名，如"1. HelloText.php"，详见[文件名与类名规则](#文件名与类名规则)

### **verify** ###

判断接收到的消息是否符合当前功能，不覆盖则默认为false。返回true时表示执行invoke方法内的操作。可根据消息条件判断，如用户发送文本为"hello"时才执行此功能：

	public function verify() {
	  return $this->map['Content']=='hello';
	}

$this->map包含了消息体的各参数，详见下述[消息体结构参数](#消息体结构参数)。

### **invoke** ###

当verify返回true时执行此方法。为了封装为xml格式字符串，可调用Wlight框架内定方法：

	protected final function makeText($text)
	protected final function makeImage($mediaId)
	protected final function makeVoice($mediaId)
	protected final function makeVideo($mediaId, title='', $description='')
	protected final function makeMusic($title, $description, $musicUrl, $hqMusicUrl='', $thumbMediaId='')

	/**
	* 封装图文消息
	* @param array $articles - (二维数组)图文内容, 包含字段: Title, Description, PicUrl, Url
	* @example array(
	*      array('Title'=>'1', 'Description'=>'', 'PicUrl'=>'1.jpg', 'Url'=>'')
	*     )
	*/
	protected final function makeNews($articles)

	/**
	* 转到多客服
	* @param string $account - 可选,指定转发到的客服帐号
	* @return string - response xml
	*/
	protected final function sendToService($account='')

以上方法具体参数请参考[微信公众平台开发者文档-被动回复用户消息](http://mp.weixin.qq.com/wiki/1/6239b44c206cab9145b1d52c67e6c551.html)。不需要回复任何内容时返回$this->emptyResponse()。

### **tag** ###

此功能的标签，用于统计该功能每天被使用的次数。默认返回null，表示此功能不需要加入统计。此方法可不覆盖。参考[框架机制-功能统计](#功能统计)

> 接收到的消息符合verify中条件并执行invoke，则视为该功能被执行了一次

### **cache** ###

表示当前功能是否需要加入缓存，true表示需要。默认返回true，可以不覆盖此方法。目前仅支持MsgType为text及event/CLICK的消息进行缓存。

> 启用缓存可加快脚本执行速度，避免无效的匹配判断，详见[框架机制](#框架机制)

### **引入API类库** ###

在Response子类中，开发者可导入框架中针对微信公众平台API接口封装的类库。具体方法为调用import方法（此方法不能被覆盖）：

	/**
	* 引入一个类库文件,并返回该类实例对象
	* @param $namespace - 类所在的空间
	* @param $className - 类名
	* @return 实例对象
	*/
	protected final function import($namespace, $className)

具体例子详见[API类库-引入API类](#引入api类)

### **消息体结构参数** ###

当接收到微信服务器发送的xml格式后，Wlight框架会自动解析xml并将消息包内的参数存入数组（消息结构请参考[微信公众平台开发者文档-接收普通消息](http://mp.weixin.qq.com/wiki/17/f298879f8fb29ab98b2f2971d42552fd.html)及[微信公众平台开发者文档-接收事件推送](http://mp.weixin.qq.com/wiki/7/9f89d962eba4c5924ed95b513ba69d9b.html)）。开发中可由$this->map获取，如获取MsgType参数则为$this->map['MsgType']。

### **文件名与类名规则** ##

在/message/中的子目录下新建php脚本时，Wlight要求文件名与文件里的类名相同（不相同会导致找不到对应的类，则跳过该脚本），如上述例子中"HelloText.php"需对应类名为"HelloText"。

由于遍历执行判断影响效率，除了开启缓存外，开发者还可以根据需求判断哪一个功能被使用得较多（或从统计中得出结果），从而将该功能对应的php文件改名，使其处于所有文件名升序排列后靠前的位置。Wlight会忽略**文件名中首个字母前的所有字符**，以及文件.php后缀名。建议在类名前加入"1. "或"1\_"作前缀。

> 例：现有AccessToken.php和HelloText.php脚本，默认升序为AccessToken先于HelloText。此时可加入前缀更改两个脚本的顺序，如改为"2. AccessToken.php"和"1. HelloText.php"，则Wlight会先匹配HelloText。若HelloText判断条件返回true，则执行HelloText中的invoke方法，而不会继续遍历AccessToken。


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


# **API类库** #

Wlight框架中的API类是对微信公众平台API接口的封装，开发者不需要根据官方文档自己发送Http请求，只需调用类库中的方法即可。

> 尽管Wlight框架对大部分API类进行封装，但还是希望开发者在熟悉官方文档后再使用类库。同时建议在使用类库开发过程中参考对应的官方文档

## **命名空间与分类** ##

API类库位于/wlight/library/api，辅助类位于/wlight/library/util。为了使开发者更方便地导入不同的类，Wlight根据命名空间分类各个接口类：（以下，左侧为空间名，右侧为类名）

- **basic**: AccessToken, IpList, JsapiTicket
- **customservice**: Account, Message
- **media**: Media
- **menu**: Menu, MenuDesigner
- **user**: Groups, Info
- **web**: JsapiTicket, Jssdk, Oauth, OauthRedirect
- **util**: DbHelper, HttpClient

## **导入API类** ##

开发者可调用框架中的接口方法，根据命名空间和类名导入相应的类。

在Response继承类中，可调用$this->import($namespace, $className)：

	/**
	* 引入一个类库文件,并返回该类实例对象
	* @param $namespace - 类所在的空间
	* @param $className - 类名
	* @return 实例对象
	*/
	protected final function import($namespace, $className)

可在覆盖invoke的方法体中调用import方法。以下例子展示了在invoke中返回有效access_token值：

	public function invoke() {
	  $accessTokenClass = $this->import('basic', 'AccessToken');
	  $accessToken = $accessTokenClass->get();
	  return $this->makeText($accessToken);
	}

在外部应用中（即不是自动回复开发，如/application中的web app网站，其入口不是index.php），由于无法继承Response类，不能调用$this->import方法，但可通过\wlight\dev\Library类，调用其import方法实现相同的效果。Library.import为静态方法。**外部应用必须通过Library::import的方式引入API类，不能根据目录直接使用include，否则会导致路径出错。**
	
	//假设在/application/index.php中, 需先引入Library.class.php
	include(../wlight/develop/Library.class.php);

	$accessTokenClass = \wlight\dev\Library::import('basic', 'AccessToken');
	$accessToken = $accessTokenClass->get();

Response.import和Library.import不在同一文件中，但两者参数定义与内部实现相同。其中，$namespace为类所在的命名空间，$className为类名。通过import方法，开发者不需考虑类文件的路径及命名空间，只需根据import的返回结果新建类即可。

> import方法内部实现通过$namespace和$class定位到类文件，然后调用include_once()导入该文件，并返回该类实例。

## **异常处理** ##

所有API接口均会抛出ApiException异常。这个异常是由于Http请求出错、json格式出错、上传文件不存在等情况触发的。开发者必须在所有涉及API类操作（从new创建实例开始）中加入捕获异常的语句。任何未捕获的异常均导致脚本中止运行。

ApiException中框架定义的错误码及说明如下：

- **HTTP\_ERROR\_CODE**: -101，Http请求出错
- **JSON\_DECODE\_ERROR\_CODE**: -102，json格式出错
- **ILLEGAL\_JSON\_ERROR\_CODE**: -103，json内含字段出错
- **FILE\_NOT\_EXISTS\_ERROR\_CODE**: -104，上传文件不存在
- **OAUTH\_REJECT\_ERROR\_CODE**: -105，用户拒绝授权

其他错误码请参考[微信公众平台开发者文档-全局返回码说明](http://mp.weixin.qq.com/wiki/17/fa4e1434e57290788bde25603fa2fcbd.html)

ApiException针对错误信息提供操作方法，在捕获异常后可调用：

- **log**：错误信息输出到日志(/runtime/log/error中)。由于日志仅供自动回复功能使用，在外部应用中调用此方法无效
- **getInfo**：获取错误信息的字符串
- **printInfo**：打印错误信息到响应正文

以下例子展示在Response.invoke方法中捕获ApiException并输出到日志：

	<?php
	  namespace wlight\msg;
	  use wlight\core\Response;
	  use wlight\runtime\ApiException;	//必须加入此语句
	
	  ...继承Response, 重写verify

	  public function invoke() {
		try {
		  $accessTokenClass = $this->import('basic', 'AccessToken');
		  $accessToken = $accessTokenClass->get();
		  return $this->makeText($accessToken);
		} catch (ApiException $e) {
		  $e->log();
		  return $this->makeText('无法获取AccessToken');
		}
	  }
	?>

********************


# **附录1：API类库接口规范** #

## **basic** ##

[获取access\_token](http://mp.weixin.qq.com/wiki/11/0e4b294685f817b95cbed85ba5e82b8f.html)、[获取微信服务器IP地址](http://mp.weixin.qq.com/wiki/0/2ad4b6bfd29f30f71d39616c2a0fcedc.html)。

### **AccessToken** ###

[获取access_token](http://mp.weixin.qq.com/wiki/11/0e4b294685f817b95cbed85ba5e82b8f.html)

	/**
	 * 获取Access Token(或刷新Token值)
	 * @param boolean $reload - true表示重新获取最新Token值
	* @return string - token字符串(请求失败返回false)
	* @throws ApiException
	 */
	public function get($reload = false)

### **IpList** ###

[获取微信服务器IP地址](http://mp.weixin.qq.com/wiki/0/2ad4b6bfd29f30f71d39616c2a0fcedc.html)

	/**
	* 获取IP列表
	* @return array - 对应ip地址数组(请求失败返回false)
	* @throws ApiException
	*/
	public function get()

## **customservice** ##

客服功能相关接口。

### **Account** ###

[客服帐号管理](http://mp.weixin.qq.com/wiki/1/70a29afed17f56d537c833f89be979c9.html#.E5.AE.A2.E6.9C.8D.E5.B8.90.E5.8F.B7.E7.AE.A1.E7.90.86)

	/**
	* 添加客服帐号
	* @param string $account - 客服帐号(可忽略后缀)
	* @param string $nickname - 昵称
	* @param string $password - 登录密码(未加密)
	* @return boolean - true表示成功
	* @throws ApiException
	*/
	public function add($account, $nickname, $password)
	
	/**
	* 修改客服帐号
	* @param string $account - 客服帐号(可忽略后缀)
	* @param string $nickname - 昵称
	* @param string $password - 登录密码(未加密)
	* @return boolean - true表示成功
	* @throws ApiException
	*/
	public function update($account, $nickname, $password)
	
	/**
	* 设置客服帐号头像
	* @param string $account - 客服帐号(可忽略后缀)
	* @param string $img - 头像图片文件
	* @return boolean - true表示成功
	* @throws ApiException
	*/
	public function uploadHeadimg($account, $img)
	
	/**
	* 删除客服帐号
	* @param string $account - 客服帐号(可忽略后缀)
	* @param string $nickname - 昵称
	* @return boolean - true表示成功
	* @throws ApiException
	*/
	public function delete($account, $nickname)
	
	/**
	* 获取所有客服帐号
	* @return array - 客服帐号数组(失败时返回false)
	* @throws ApiException
	*/
	public function getAll()
	
	/**
	* 获取在线客服接待信息
	* @return array - 客服接待信息集合(失败时返回false)
	* @throws ApiException
	*/
	public function getOnlineList()

### **Message** ###

[客服接口-发消息](http://mp.weixin.qq.com/wiki/1/70a29afed17f56d537c833f89be979c9.html#.E5.AE.A2.E6.9C.8D.E6.8E.A5.E5.8F.A3-.E5.8F.91.E6.B6.88.E6.81.AF)

	/**
	* 指定发送消息的客服帐号
	* @param string $account - 客服帐号
	*/
	public function setAccount($account)
	
	/**
	* 发送文本消息
	* @param string $user - 接收方
	* @param string $text - 文本消息
	* @return boolean - true表示成功
	* @throws ApiException
	*/
	public function sendText($user, $text)
	
	/**
	* 发送图片消息
	* @param string $user - 接收方
	* @param string $mediaId - 图片媒体id
	* @return boolean - true表示成功
	* @throws ApiException
	*/
	public function sendImage($user, $mediaId)
	
	/**
	* 发送语音消息
	* @param string $user - 接收方
	* @param string $mediaId - 语音媒体id
	* @return boolean - true表示成功
	* @throws ApiException
	*/
	public function sendVoice($user, $mediaId)
	
	/**
	* 发送视频消息
	* @param string $user - 接收方
	* @param string $mediaId - 视频媒体id
	* @param string $thumbMediaId - 缩略图媒体id
	* @param string $title - 可选,视频标题
	* @param string $description - 可选,视频描述
	* @return boolean - true表示成功
	* @throws ApiException
	*/
	public function sendVideo($mediaId, $thumbMediaId, $title='', $description='')
	
	/**
	* 发送音乐消息
	* @param string $user - 接收方
	* @param string $title - 音乐标题
	* @param string $description - 音乐描述
	* @param string $musicUrl - 音乐链接
	* @param string $hqMusicUrl - 音乐高品质资源链接
	* @param string $thumbMediaId - 缩略图媒体id
	* @return boolean - true表示成功
	* @throws ApiException
	*/
	public function sendMusic($title, $description, $musicUrl, $hqMusicUrl, $thumbMediaId)
	
	/**
	* 发送图文消息(跳转到链接)
	* @param string $user - 接收方
	* @param array $articles - (二维数组)图文内容, 包含字段: Title, Description, PicUrl, Url
	* @example array(
	*      array('Title'=>'1', 'Description'=>'', 'PicUrl'=>'1.jpg', 'Url'=>''))
	* @return boolean - true表示成功
	* @throws ApiException
	*/
	public function sendNews($user, $articles)
	
	/**
	* 发送图文消息(跳转到图文页面)
	* @param string $user - 接收方
	* @param string $mediaId - 图文媒体id
	* @return boolean - true表示成功
	* @throws ApiException
	*/
	public function sendMpnews($user, $mediaId)
	
	/**
	* 发送卡券
	* @param string $user - 接收方
	* @param string $cardId - 卡券id
	* @param array $cardExt - 卡券card_ext字段信息
	* @return boolean - true表示成功
	* @throws ApiException
	*/
	public function sendCard($user, $cardId, $cardExt)

## **media** ##

素材管理相关接口。

### **Media** ###

[新增临时素材](http://mp.weixin.qq.com/wiki/5/963fc70b80dc75483a271298a76a8d59.html)、[获取临时素材](http://mp.weixin.qq.com/wiki/11/07b6b76a6b6e8848e855a435d5e34a5f.html)

	/**
	* 上传一个素材
	* @param string $mediaFile - 完整(绝对路径)文件路径
	* @param string $type - 可选,上传媒体文件的类型(image、voice、video、thumb),不填则根据后缀名判断
	* @return string - mediaId媒体id(请求失败返回false)
	* @throws ApiException
	*/
	public function upload($mediaFile, $type=null)
	
	/**
	* 下载一个素材
	* @param string $mediaId - 媒体id,通过上传获得
	* @param string $toFile - 可选,下载到文件的绝对路径,不填则默认路径为RES_ROOT/$mediaId
	* @return integer - 下载到的文件大小(失败时返回false)
	* @throws ApiException
	*/
	public function download($mediaId, $toFile=null)

## **menu** ##

自定义菜单开发接口。

### **Menu** ###

[自定义菜单创建](http://mp.weixin.qq.com/wiki/10/0234e39a2025342c17a7d23595c6b40a.html)、[自定义菜单查询](http://mp.weixin.qq.com/wiki/5/f287d1a5b78a35a8884326312ac3e4ed.html)、[自定义菜单删除](http://mp.weixin.qq.com/wiki/3/de21624f2d0d3dafde085dafaa226743.html)、[个性化菜单接口](http://mp.weixin.qq.com/wiki/0/c48ccd12b69ae023159b4bfaa7c39c20.html)

	/**
	* 创建自定义菜单(默认或个性化菜单)
	* @param array $menu - 自定义菜单内容数组
	* @param array $condition - 可选, 个性化菜单的用户组条件, 不填则创建默认菜单
	* @return boolean/string - 创建默认菜单时,成功返回true;创建个性化菜单时,成功返回menuid
	* @throws ApiException
	*/
	public function create($menu, $condition=null)
	
	/**
	* 查询自定义菜单(结果包含默认和个性化菜单)
	* @param boolean $assocArray - 可选,false则直接返回API的结果(默认true返回解析后的数组)
	* @return string/array - 查询后的结果
	* @throws ApiException
	*/
	public function get($assocArray = true)
	
	/**
	* 删除自定义菜单(默认或个性化)
	* @param string $menuId - 可选,个性化菜单的menuid,不填则删除所有菜单(包括默认和个性化)
	* @return boolean - 删除成功时返回true
	* @throws ApiException
	*/
	public function delete($menuId=null)
	
	/**
	* 测试个性化菜单
	* @param string $userId - 用户openId或微信号
	* @param boolean $assocArray - 可选,false则直接返回API的结果(默认true返回解析后的数组)
	* @return string/array - 查询后的结果
	*/
	public function test($userId, $assocArray=true)

### **MenuDesigner** ###

菜单设计辅助类

	/**
	* 获取通过本类方法生成的菜单数组
	* @return array - 菜单数组
	*/
	public function getMenu()
	
	/**
	* 添加一个子菜单
	* @param string $name - 子菜单标题
	* @param array $subButton - 子菜单数组, 可通过本类生成
	* @return array - 菜单生成数组
	*/
	public function addSubButton($name, $subButton)
	
	/**
	* 添加一个CLICK类型菜单
	* @param string $name - 菜单标题
	* @param string $key - 菜单key值
	* @return array - 菜单生成数组
	*/
	public function addClick($name, $key)
	
	/**
	* 添加一个VIEW类型菜单
	* @param string $url - 网页链接
	* @param string $name - 菜单标题
	* @return array - 菜单生成数组
	*/
	public function addView($name, $url)
	
	/**
	* 添加一个扫码类型菜单
	* @param string $name - 菜单标题
	* @param string $key - 菜单key值
	* @param string $type - 可选,扫码操作类型,可填PUSH或WAITMSG
	* @return array - 菜单生成数组
	*/
	public function addScan($name, $key, $type='scancode_push')
	
	/**
	* 添加一个发图类型菜单
	* @param string $name - 菜单标题
	* @param string $key - 菜单key值
	* @param string $type - 可选,发图类型,可填SYSPHOTO或PHOTO_OR_ALBUM或WEIXIN
	* @return array - 菜单生成数组
	*/
	public function addPic($name, $key, $type='pic_photo_or_album')
	
	/**
	* 添加一个发送位置类型菜单
	* @param string $name - 菜单标题
	* @param string $key - 菜单key值
	* @return array - 菜单生成数组
	*/
	public function addLocation($name, $key)

## **user** ##

用户与分组管理相关接口。

### **Groups** ###

[用户分组管理](http://mp.weixin.qq.com/wiki/0/56d992c605a97245eb7e617854b169fc.html)

	/**
	* 创建分组
	* @param string $name - 分组名
	* @return integer - 分组id(失败时返回false)
	* @throws ApiException
	*/
	public function create($name)
	
	/**
	* 查询所有分组
	* @return array - 分组数组(失败时返回false)
	* @throws ApiException
	*/
	public function getAll()
	
	/**
	* 查询用户所在的分组
	* @param string $openId - 用户openid
	* @return integer - 分组id(失败时返回false)
	* @throws ApiException
	*/
	public function queryUser($openId)
	
	/**
	* 修改分组名
	* @param string $groupId - 分组id
	* @param string $name - 分组名
	* @return boolean - true表示成功
	* @throws ApiException
	*/
	public function update($groupId, $name)
	
	/**
	* 移动用户分组
	* @param string/array $openidList - 用户openid的列表(不超过50)
	* @param string $toGroupId - 目标分组id
	* @return boolean - true表示成功
	* @throws ApiException
	*/
	public function moveUpser($openidList, $toGroupId)
	
	/**
	* 删除分组
	* @param string $groupId - 分组id
	* @return boolean - true表示成功
	* @throws ApiException
	*/
	public function delete($groupId)

### **Info** ###

[获取用户基本信息](http://mp.weixin.qq.com/wiki/14/bb5031008f1494a59c6f71fa0f319c66.html)、[获取用户openid列表](http://mp.weixin.qq.com/wiki/0/d0e07720fc711c02a3eab6ec33054804.html)、[设置用户备注名](http://mp.weixin.qq.com/wiki/1/4a566d20d67def0b3c1afc55121d2419.html)

	/**
	* 获取用户信息
	* @param string/array $openId - 用户openid列表数组(不超过100个)
	* @param string $language - 可选,语言版本(zh_CN, zh_TW, en)
	* @return array - 用户信息列表数组(请求失败返回false)
	* @throws ApiException
	*/
	public function get($openId, $language='zh_CN')
	
	/**
	* 从头拉取用户的openid列表(最多拉取10000个)
	* @return array - 接口返回结果集合,包含总关注数、本次拉取数及openid列表
	* @throws ApiException
	*/
	public function getUserListFromStart()
	
	/**
	* 获取用户的openid列表(每次最多拉取10000个)
	* @param string $fromOpenId - 起始openid,不填写代表接上次结果继续拉取
	* @return array - 接口返回结果集合,包含总关注数、本次拉取数及openid列表
	* @throws ApiException
	*/
	public function getUserList($fromOpenId='')

	/**
	* 设置用户备注名
	* @param string $openId - 用户openid
	* @param string $remark - 备注名, 小于30字符
	* @return boolean - 设置成功返回true
	* @throws ApiException
	*/
	public function setRemark($openId, $remark)

## **web** ##

Web开发相关接口。

### **JsapiTicket** ###

[获取JsapiTicket(调用js接口凭证)](http://mp.weixin.qq.com/wiki/11/74ad127cc054f6b80759c40f77ec03db.html#.E9.99.84.E5.BD.951-JS-SDK.E4.BD.BF.E7.94.A8.E6.9D.83.E9.99.90.E7.AD.BE.E5.90.8D.E7.AE.97.E6.B3.95)

	/**
	* 获取Jsapi Ticket(或刷新Ticket值)
	* @param boolean $reload - true表示重新获取最新Ticket值
	* @return string - token字符串(请求失败返回false)
	* @throws ApiException
	*/
	public function get($reload = false)

### **Jssdk** ###

[微信内网页开发功能(jssdk)开发类库](http://mp.weixin.qq.com/wiki/11/74ad127cc054f6b80759c40f77ec03db.html)

	/**
	* 设置调试模式
	* @param boolean $debug - true为开启调试模式
	*/
	public function setDebug($debug)
	
	/**
	* 获取jsapi接口的配置信息
	* @param string/array $apiList - 需要使用的JS接口列表
	* @return string - 验证配置对应的js语句,可直接在js脚本中使用
	*/
	public function config($apiList)
	
	/**
	* 获取引入js文件的路径
	* @param string $version - 可选,引入文件的版本号,默认1.0.0
	* @return string - js文件路径
	*/
	public function getReference($version = '1.0.0')
	
	/**
	* 获取引入js文件的标签
	* @param string $version - 可选,引入文件的版本号,默认1.0.0
	* @return string - js文件标签
	*/
	public function getReferenceLabel($version = '1.0.0')
	
	/**
	* 获取权限签名
	* @return array - 权限签名数组,包含appId、signature等字段
	*/
	public function getSignPackage()

### **Oauth** ###

[网页授权获取用户信息的接口](http://mp.weixin.qq.com/wiki/4/9ac2e7b1f1d22e9e57260f6553822520.html)
	
	/**
	* 设置回调后重定向url
	* @param string $redirectUrl - 重定向url
	*/
	public function setRedirectUrl($redirectUrl)

	/**
	* 获取scope为snsapi_basic的重定向路径(只能获取openId)
	* @param string $extraString - 可选,开发者额外参数
	* @return string - 重定向路径
	*/
	public function getBasic($extraString = '')
	
	/**
	* 获取scope为snsapi_userinfo的重定向路径(获取用户具体信息)
	* @param string $extraString - 可选,开发者额外参数
	* @return string - 重定向路径
	*/
	public function getUserInfo($extraString = '')

### **OauthRedirect** ###

[网页授权的回调处理脚本(非框架接口)](http://mp.weixin.qq.com/wiki/4/9ac2e7b1f1d22e9e57260f6553822520.html#.E7.AC.AC.E4.BA.8C.E6.AD.A5.EF.BC.9A.E9.80.9A.E8.BF.87code.E6.8D.A2.E5.8F.96.E7.BD.91.E9.A1.B5.E6.8E.88.E6.9D.83access_token)

	/**
	* 获取基本信息(access_token及openid)
	* @return array - 基本信息数组
	*/
	public function getBasic()
	
	/**
	* 获取用户详细信息
	* @param string $language - 可选,用户语言版本
	* @return array - 详细信息数组
	*/
	public function getUserInfo($language='zh_CN')