<?php
/**
 * 缓存锁
 * @author  KavMors(kavmors@163.com)
 * @since   2.1
 */

namespace wlight\core\support;

class Locker {
	private static $lockers;

	public static function getInstance($file) {
		if (self::$lockers == null) {
			self::$lockers = array();
		}
		if (!isset(self::$lockers[$file])) {
			self::$lockers[$file] = new Locker($file);
		}
		return self::$lockers[$file];
	}

	private $locker;
	private $isLocked;

	private function __construct($file) {
		$this->locker = fopen($file, 'r');
		$this->isLocked = false;
	}

	public function lock() {
		flock($this->locker, LOCK_EX);
		$this->isLocked = true;
	}

	public function unlock() {
		if ($this->isLocked) {
			flock($this->locker, LOCK_UN);
    	fclose($this->locker);
    	$this->isLocked = false;
		}
	}
}

?>