<?php

	class Properize {

		static protected $_lang;

		public static function setLanguage($lang) {
			static::$_lang = $lang;
		}

		public static function prop($string, $language = null) {
			if ($language === null) {
				$language = static::$_lang;
			}
			$_method = '_properize' . ucfirst($language);
			if (!method_exists(get_class(), $_method)) {
				throw new InvalidArgumentException("Properize: unknown language '$language'");
			}
			return static::$_method($string);
		}

		protected static function _properizeEng($string) {
			$suffix = '’s';
			$apo = ['S' => 1, 's' => 1];
			if (isset($apo[mb_substr($string, -1)])) {
				$suffix = '’';
			}
			return $string . $suffix;
		}

		protected static function _properizeDeu($string) {
			$suffix = 's';
			$apo = ['S' => 1, 's' => 1, 'ß' => 1, 'x' => 1, 'z' => 1];

			if (isset($apo[mb_substr($string, -1)])) {
				// Hans’ Tante
				$suffix = '’';
			} elseif ('ce' === (mb_substr($string, -2))) {
				// Alice’ Tante
				$suffix = '’';
			}

			return $string . $suffix;
		}

	}