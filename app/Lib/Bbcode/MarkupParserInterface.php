<?php

	/**
	 * @td Configure::read('Saito.Settings') should be argument or
	 * make raw BBC class and a Subclass incorporating functions with 'Saito.Settings'
	 */
	interface MarkupParserInterface {

		public function parse($string);

		public function citeText($string);

	}

