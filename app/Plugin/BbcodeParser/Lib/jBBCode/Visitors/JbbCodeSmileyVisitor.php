<?php

	App::uses('JbbCodeTextVisitor', 'BbcodeParser.Lib/jBBCode/Visitors');

	/**
	 * Class JbbCodeSmileyVisitor replaces ASCII smilies with images
	 */
	class JbbCodeSmileyVisitor extends JbbCodeTextVisitor {

		public function __construct(\Helper $Helper, array $_sOptions) {
			parent::__construct($Helper, $_sOptions);
			$this->_useCache = !Configure::read('debug');
		}

		protected function _processTextNode($string, $node) {
			return $this->_sHelper->SmileyRenderer->replace($string);
		}

	}
