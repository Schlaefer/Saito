<?php

	App::uses('AppHelper', 'View/Helper');

	class TextHHelper extends AppHelper {

		public function properize($string, $language = 'de') {
			$suffix = 's';
			$apo = array('S' => 1, 's' => 1, 'ß' => 1, 'x' => 1, 'z' => 1);

			if (isset($apo[mb_substr($string, -1)])) {
				// Hans’ Tante
				$suffix = '’';
			} elseif ('ce' == (mb_substr($string, -2))) {
				// Alice’ Tante
				$suffix = '’';
			}

			return $string . $suffix;
		}

	}
