<?php

	namespace Saito\Markup;

	abstract class Preprocessor {

		protected $_settings;

		public function __construct($settings) {
			$this->_settings = $settings;
		}

		/**
		 * preprocess markup before it's persistently stored
		 *
		 * @param $string
		 * @return string
		 */
		abstract public function process($string);

	}