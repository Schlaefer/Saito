<?php

	App::uses('BbcodePreprocessorInterface', 'Lib/Bbcode/Processors');

	class BbcodeProcessorCollection {

		protected $_Processors = [];

		public function add($Preprocessor, array $options = []) {
			$options += ['priority' => 1000];
			$this->_Processors[$options['priority']][] = $Preprocessor;
		}

		public function process($string, array $options = []) {
			foreach ($this->_Processors as $priority) {
				foreach ($priority as $Preprocessor) {
					$string = $Preprocessor->process($string, $options);
				}
			}
			return $string;
		}

	}