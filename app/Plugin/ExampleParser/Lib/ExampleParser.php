<?php

	App::uses('SaitoMarkupParser', 'Lib/Saito/Markup');

	class ExampleParser extends SaitoMarkupParser {

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