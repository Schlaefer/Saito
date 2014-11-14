<?php

	App::uses('ModelBehavior', 'Model');

	class MarkupBehavior extends ModelBehavior {

		/**
		 * @var Saito\Markup\Preprocessor
		 */
		protected $_Preprocessor;

		/**
		 * @param Model $Model
		 * @param $string
		 * @return string
		 */
		public function prepareMarkup(Model $Model, $string) {
			if (empty($string)) {
				return $string;
			}
			return $this->_getPreprocessor()->process($string);
		}

		protected function _getPreprocessor() {
			if ($this->_Preprocessor === null) {
				$settings = Configure::read('Saito.Settings.Parser');
				$this->_Preprocessor = \Saito\Plugin::getParserClassInstance(
					'Preprocessor',
					$settings
				);
			}
			return $this->_Preprocessor;
		}

	}
