<?php

	namespace Plugin\ExampleParser\Lib;

	class Preprocessor extends \Saito\Markup\Preprocessor {

		/**
		 * Transform markup before it is saved into database
		 *
		 * @param $string
		 * @return string
		 */
		public function process($string) {
			return $string;
		}

	}