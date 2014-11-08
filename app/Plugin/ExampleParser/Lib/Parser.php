<?php

	namespace Plugin\ExampleParser\Lib;

	class Parser extends \Saito\Markup\Parser {

		/**
		 * Transform markup to HTML here
		 *
		 * Make sure to escape HTML special chars, or you'll have a bad day!!
		 *
		 * @param string $string unescaped markup
		 * @param array $settings
		 * @return string Make sure to escape HTML special chars, or you'll have a bad day!!
		 */
		public function parse($string, array $settings = []) {
			return $string;
		}

	}