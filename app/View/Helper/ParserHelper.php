<?php

	App::uses('AppHelper', 'View/Helper');

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
			'Text',
			//= usefull in Parsers
			'Layout',
			'SaitoHelp'
		];

		protected $_MarkupEditor;

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

		public function citeText($string) {
			return $this->_getParser()->citeText($string);
		}

		public function editorHelp() {
			return $this->_getMarkupEditor()->getEditorHelp();
		}

		public function getButtonSet() {
			return $this->_getMarkupEditor()->getMarkupSet();
		}

		public function parse($string, array $options = []) {
			Stopwatch::start('ParseHelper::parse()');
			if (empty($string) || $string === 'n/t') {
				Stopwatch::stop('ParseHelper::parse()');
				return $string;
			}

			$defaults = ['return' => 'html', 'multimedia' => true, 'wrap' => true];
			$options += $defaults;

			$cacheId = md5(serialize($options) . $string);
			if (isset($this->_parserCache[$cacheId])) {
				$html = $this->_parserCache[$cacheId];
			} else {
				$html = $this->_getParser()->parse($string, $options);
				$this->_parserCache[$cacheId] = $html;
			}
			if ($options['wrap']) {
				$html = '<div class="richtext">' . $html . '</div>';
			}
			Stopwatch::stop('ParseHelper::parse()');
			return $html;
		}

		protected function _getMarkupEditor() {
			if ($this->_MarkupEditor === null) {
				$this->_MarkupEditor = \Saito\Plugin::getParserClassInstance('Editor', $this);
			}
			return $this->_MarkupEditor;
		}

		protected function _getParser() {
			if ($this->_Parser === null) {
				$settings = Configure::read('Saito.Settings') + $this->settings;

				$this->SmileyRenderer = new \Saito\Smiley\Renderer($settings['smiliesData']);
				$this->SmileyRenderer->setHelper($this);

				$this->_Parser = \Saito\Plugin::getParserClassInstance('Parser', $this, $settings);
			}
			return $this->_Parser;
		}

	}