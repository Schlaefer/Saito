<?php

	App::uses('ReadPostingsAbstract', 'Lib/SaitoUser/ReadPostings');

	/**
	 * Nothing can be set read
	 *
	 * used as dummy for bots and test cases
	 */
	class ReadPostingsDummy extends ReadPostingsAbstract {

		public function __construct() {
		}

		public function set($entries) {
			return;
		}

		public function delete() {
			return;
		}

		public function isRead($posting, $key = 'time') {
			return true;
		}

		protected function _get() {
			return [];
		}

	}
