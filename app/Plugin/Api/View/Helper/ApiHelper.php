<?php

	App::uses('Helper', 'View');

	class ApiHelper extends Helper {

		public function mysqlTimestampToIso($date) {
			$unixTimeStamp = strtotime($date);
			if ($unixTimeStamp < 0) {
				$unixTimeStamp = 0;
			}
			return date('c', $unixTimeStamp);
		}

	}
