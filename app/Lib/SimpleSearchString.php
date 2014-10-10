<?php

	class SimpleSearchString {

		protected $_length = 4;

		protected $_operators = '+-';

		protected $_string;

		public function __construct($string, $length = null) {
			$this->_string = $string;
			if ($length) {
				$this->_length = $length;
			}
		}

		/**
		 * @param mixed $string
		 */
		public function setString($string) {
			$this->_string = $string;
		}

		/**
		 * Checks if search words have minimal word length
		 */
		public function validateLength() {
			if (empty($this->_string)) {
				return false;
			}
			return strlen($this->getOmittedWords()) === 0;
		}

		public function getOmittedWords() {
			$filter = function ($word) {
				return mb_strlen($word) < $this->_length;
			};
			$string = preg_replace('/(".*")/', '', $this->_string);
			$words = $this->_split($string);
			$result = array_filter($words, $filter);
			return implode(' ', $result);
		}

		protected function _split($string) {
			return preg_split("/(^|\s+)[{$this->_operators}]?/", $string, -1,
				PREG_SPLIT_NO_EMPTY);
		}

		/**
		 * replaces whitespace with '+'
		 *
		 * whitespace should imply AND (not OR)
		 *
		 * @return string
		 */
		public function replaceOperators() {
			$string = $this->_fulltextHyphenFix($this->_string);
			return ltrim(preg_replace('/(^|\s)(?![-+><])/i', ' +', $string));
		}

		/**
		 * @see: http://bugs.mysql.com/bug.php?id=2095
		 */
		protected function _fulltextHyphenFix($string) {
			$words = $this->_split($string);
			foreach ($words as $word) {
				$first = mb_substr($word, 0, 1);
				if ($first === '"' ) {
					continue;
				}
				if (mb_strpos($word, '-') === false) {
					continue;
				}
				$string = str_replace($word, '"' . $word . '"', $string);
			}
			return $string;
		}

	}