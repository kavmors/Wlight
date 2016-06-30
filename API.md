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
