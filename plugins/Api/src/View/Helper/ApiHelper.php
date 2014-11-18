<?php

	namespace Api\View\Helper;

	use Cake\View\Helper;


	class ApiHelper extends Helper {

		public $helpers = [
			'TimeH'
		];

		public function mysqlTimestampToIso($date) {
			if (empty($date)) {
				$date = 0;
			}
			return $this->TimeH->mysqlTimestampToIso($date);
		}

	}
