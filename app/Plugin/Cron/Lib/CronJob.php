<?php

	class CronJob {

		public $uid;

		public $due;

		protected $_garbage;

		public function __construct($uid, $due, callable $func) {
			$this->uid = $uid;
			$this->due = $due;
			$this->_garbage = $func;
		}

		public function execute() {
			call_user_func($this->_garbage);
		}

	}
