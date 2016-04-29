<?php
/**
 * 缓存锁
 * @author  KavMors(kavmors@163.com)
 * @since   2.1
 */

namespace wlight\core\support;

class LockWorker {
	private $locker;

	public function __construct($file) {
		$this->locker = fopen($file, 'r');
	}

	public function lock() {
		flock($this->locker, LOCK_EX);
	}

	public function unlock() {
		flock($this->locker, LOCK_UN);
    fclose($this->locker);
	}
}