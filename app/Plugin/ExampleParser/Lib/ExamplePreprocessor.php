<?php

	App::uses('SaitoMarkupPreprocessor', 'Lib/Saito/Markup');

	class ExamplePreprocessor extends SaitoMarkupPreprocessor {

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