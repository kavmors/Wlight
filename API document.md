# **API类库接口规范** #

## **basic** ##

基础支持功能接口。

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