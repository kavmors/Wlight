<?php
/**
 * 缓存文件读写器
 * @author  KavMors(kavmors@163.com)
 * @since   2.1
 */

namespace wlight\core\support;

class RecordManager {
	private $cache;
	private $isCreated;

	public function __construct($file) {
		if (substr($file, -4) != '.php') {
			$file .= '.php';
		}
		$this->cache = $file;
		$this->isCreated = false;
		if (!file_exists($file)) {
			file_put_contents($file, "<?php exit; ?>\n");
			$this->isCreated = true;
		}
		@chmod($file, 0775);
	}

	/**
	 * 初始化时是否创建文件
	 * @return boolean - 创建了文件则返回true
	 */
	public function isCreatedFile() {
		return $this->isCreated;
	}

	/**
	 * 写入缓存文件, 覆盖
	 * @param string $str - 缓存文本
	 */
	public function write($str) {
		$str = "<?php exit; ?>\n".$str;
		file_put_contents($this->cache, $str);
	}

	/**
	 * 写入缓存文件, 追加
	 * @param string $str - 缓存文本
	 */
	public function append($str) {
		file_put_contents($this->cache, $str, FILE_APPEND);
	}

	/**
	 * 读取缓存文件中的内容
	 * @return string - 缓存内容
	 */
	public function read() {
		$content = file_get_contents($this->cache);
		$content = substr($content, stripos($content, '?>')+2);
		return trim($content);
	}
}

?>