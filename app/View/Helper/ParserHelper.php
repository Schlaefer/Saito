<?php

	App::uses('AppHelper', 'View/Helper');
	App::uses('SaitoSmileyRenderer', 'Lib/Saito');
	App::uses('SaitoPlugin', 'Lib/Saito');

	class ParserHelper extends AppHelper {

		/**
		 * @var array these Helpers are also used in the Parser
		 */
		public $helpers = [
			'FileUpload.FileUpload',
			'MailObfuscator.MailObfuscator',
			'Geshi.Geshi',
			'Embedly.Embedly',
			'Html',
			'Text'
		];

		/**
		 * @var array parserCache for parsed markup
		 *
		 * Esp. useful for repeating signatures in long mix view threads
		 */
		protected $_parserCache = [];

		protected $_Parser;

		public $SmileyRenderer;

		public function beforeRender($viewFile) {
			if (isset($this->request) && $this->request->action === 'preview') {
				$this->Geshi->showPlainTextButton = false;
			}
		}

		public function parse($string, array $options = []) {
			Stopwatch::start('ParseHelper::parse()');
			if (empty($string) || $string === 'n/t') {
				Stopwatch::stop('ParseHelper::parse()');
				return $string;
			}

			$defaults = ['return' => 'html', 'multimedia' => true];
			$options += $defaults;

			$cacheId = md5(serialize($options) . $string);
			if (isset($this->_parserCache[$cacheId])) {
				Stopwatch::stop('ParseHelper::parse()');
				return $this->_parserCache[$cacheId];
			}

			$this->_parserCache[$cacheId] = $this->_getParser()->parse($string, $options);
			Stopwatch::stop('ParseHelper::parse()');
			return $this->_parserCache[$cacheId];
		}

		public function citeText($string) {
			return $this->_getParser()->citeText($string);
		}

		protected function _getParser() {
			if ($this->_Parser === null) {
				$settings = Configure::read('Saito.Settings') + $this->settings;

				$this->SmileyRenderer = new SaitoSmileyRenderer($settings['smiliesData']);
				$this->SmileyRenderer->setHelper($this);

				$this->_Parser = SaitoPlugin::getParserClassInstance('Parser', $this, $settings);
			}
			return $this->_Parser;
		}

	}