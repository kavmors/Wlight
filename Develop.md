# **开发** #

修改框架基本配置信息后，即可接入公众平台的开发功能，包括针对公众平台网站的配置、基本自动回复功能。

## **平台部署** ##

部署前，填修改index.php中配置项，并保证文件系统权限（对所有文件夹及子文件有读写权限）和数据库权限（创表、删表、改结构、查结构、增删改查）。若无创建数据库权限，请手动创建数据库并赋予用户对该库的所有权限。

在公众平台开发配置中，将对应的配置值填写并提交。其中，URL对应本框架根目录中index.php。提交成功后，框架所有目录及数据库自动生成。

在调试模式下（DEBUG\_MODE=true），开发者可直接访问index.php检验目录及数据库是否生成，结合日志排查所有报错。在生产环境下建议将调试模式关闭。

> index.php文件仅为接收微信平台消息的入口，出于安全考虑，可更改此文件名称，更改后只需在公众平台网站配置中将URL修改为对应的路径即可。

## **自动回复开发** ##

成功接入并启用开发者功能后，可根据业务需求开发自动回复功能。此处涉及根目录下的/message文件夹。文件夹内子目录名称代表根据接收消息的MsgType分类（如/message/text/表示当用户发送的消息为Text文本时执行这个子目录下的脚本）。根据接收消息的MsgType，在对应的子目录下新建php脚本，创建继承自Response类的子类及覆盖相关方法即可。一个php脚本文件对应一个回复功能。

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

此功能的标签，用于统计该功能每天被使用的次数。默认返回null，表示此功能不需要加入统计。此方法可不覆盖。参考[框架机制-功能统计](./Core#功能统计)

> 接收到的消息符合verify中条件并执行invoke，则视为该功能被执行了一次

### **cache** ###

表示当前功能是否需要加入缓存，true表示需要。默认返回true，可以不覆盖此方法。目前仅支持MsgType为text及event/CLICK的消息进行缓存。

> 启用缓存可加快脚本执行速度，避免无效的匹配判断，详见[框架机制](./Core)

### **引入API类库** ###

在Response子类中，开发者可导入框架中针对微信公众平台API接口封装的类库。具体方法为调用import方法（此方法不能被覆盖）：

	/**
	* 引入一个类库文件,并返回该类实例对象
	* @param $namespace - 类所在的空间
	* @param $className - 类名
	* @return 实例对象
	*/
	protected final function import($namespace, $className)

具体例子详见[API类库-导入API类](./API#导入api类)

### **消息体结构参数** ###

当接收到微信服务器发送的xml格式后，Wlight框架会自动解析xml并将消息包内的参数存入数组（消息结构请参考[微信公众平台开发者文档-接收普通消息](http://mp.weixin.qq.com/wiki/17/f298879f8fb29ab98b2f2971d42552fd.html)及[微信公众平台开发者文档-接收事件推送](http://mp.weixin.qq.com/wiki/7/9f89d962eba4c5924ed95b513ba69d9b.html)）。开发中可由$this->map获取，如获取MsgType参数则为$this->map['MsgType']。

### **文件名与类名规则** ##

在/message/中的子目录下新建php脚本时，Wlight要求文件名与文件里的类名相同（不相同会导致找不到对应的类，则跳过该脚本），如上述例子中"HelloText.php"需对应类名为"HelloText"。

由于遍历执行判断影响效率，除了开启缓存外，开发者还可以根据需求判断哪一个功能被使用得较多（或从统计中得出结果），从而将该功能对应的php文件改名，使其处于所有文件名升序排列后靠前的位置。Wlight会忽略**文件名中首个字母前的所有字符**，以及文件.php后缀名。建议在类名前加入"1. "或"1\_"作前缀。

> 例：现有AccessToken.php和HelloText.php脚本，默认升序为AccessToken先于HelloText。此时可加入前缀更改两个脚本的顺序，如改为"2. AccessToken.php"和"1. HelloText.php"，则Wlight会先匹配HelloText。若HelloText判断条件返回true，则执行HelloText中的invoke方法，而不会继续遍历AccessToken。

### **全局Hook机制** ###

Hook机制用于监听全局自动回复事件，在自动回复执行前(后)进行相关操作。可用于修改消息体内容(回复执行前)或回复消息的内容(回复执行后)。

初始化部署完成后，框架会生成/message/Hook.php文件。在Hook类相关方法中写入逻辑即可实现对自动回复事件的监听。

	/**
	* 在所有自动回复前执行
	* @param array &$map - 执行前的数据对象(来自微信服务器)
	*/
	public function onPreExecute(&$map)

	/**
	* 在所有自动回复后执行
	* @param string &$result - 执行后的xml字符串(向微信服务器回复的内容)
	*/
	public function onPostExecute(&$result)
