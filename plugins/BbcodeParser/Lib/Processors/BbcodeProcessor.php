<?php

	namespace Plugin\BbcodeParser\Lib\Processors;

	abstract class BbcodeProcessor {

		protected $_sOptions;

		public function __construct(array $options = []) {
			$this->_sOptions = $options;
		}

		public abstract function process($string);

	}