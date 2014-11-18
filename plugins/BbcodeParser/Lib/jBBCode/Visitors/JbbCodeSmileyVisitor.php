<?php

	namespace Plugin\BbcodeParser\Lib\jBBCode\Visitors;

	use Cake\Core\Configure;
	use Cake\View\Helper;

	/**
	 * Class JbbCodeSmileyVisitor replaces ASCII smilies with images
	 */
	class JbbCodeSmileyVisitor extends JbbCodeTextVisitor {

		public function __construct(Helper $Helper, array $_sOptions) {
			parent::__construct($Helper, $_sOptions);
			$this->_useCache = !Configure::read('debug');
		}

		protected function _processTextNode($string, $node) {
			return $this->_sHelper->SmileyRenderer->replace($string);
		}

	}
