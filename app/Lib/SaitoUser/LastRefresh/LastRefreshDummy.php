<?php

	App::uses('LastRefreshAbstract', 'Lib/SaitoUser/LastRefresh');

	/**
	 * everything is always read
	 *
	 * used as dummy for bots and test cases
	 */
	class LastRefreshDummy extends LastRefreshAbstract {

		public function __construct() {
		}

		protected function _get() {
			return strtotime('+1 week');
		}

		public function set($timestamp = null) {
			return;
		}

		protected function _set() {
			return;
		}

	}