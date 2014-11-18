<?php

	namespace App\Model\Behavior;

	use Cake\Core\Configure;
	use Cake\ORM\Behavior;

	class MarkupBehavior extends Behavior {

		/**
		 * @var Saito\Markup\Preprocessor
		 */
		protected $_Preprocessor;

		/**
		 * @param Model $Model
		 * @param $string
		 * @return string
		 */
		public function prepareMarkup($string) {
			if (empty($string)) {
				return $string;
			}
			return $this->_getPreprocessor()->process($string);
		}

		/**
		 * @return Saito\Markup\Preprocessor
		 */
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
