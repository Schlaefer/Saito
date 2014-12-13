<?php

	namespace Saito\Thread\Renderer;

	/**
	 * renders posting into an ul-li HTML-list tree
	 *
	 * Check and benchmark on front-page if you perform changes here!
	 */
	abstract class HtmlRendererAbstract {

		/** * @var SaitoEventManager */
		use \Saito\Posting\Renderer\HelperTrait;

		protected $_EntryHelper;

		protected $_defaults = [
			'currentEntry' => null,
			'ignore' => true,
			'rootWrap' => false
		];

		protected $_settings;

		protected $_lastAnswer;

		protected $_SEM;

		public function __construct(\EntryHHelper $EntryHelper, $options = []) {
			$this->_EntryHelper = $EntryHelper;
			$this->_SEM = \SaitoEventManager::getInstance();
			$this->setOptions($options);
		}

		public function render(\Saito\Posting\PostingInterface $node) {
			$this->_lastAnswer = $node->getThread()->getLastAnswer();
			$html = $this->_renderNode($node);
			if ($node->isRoot() || $this->_settings['rootWrap']) {
				$html = $this->_wrapUl($html, 0, $node->get('id'));
			}
			return $html;
		}

		public function setOptions($options) {
			$this->_settings = $options + $this->_defaults;
		}

		protected function _renderNode(\Saito\Posting\PostingInterface $node) {
			$html = $this->_renderCore($node);

			$children = $node->getChildren();
			if (empty($children)) {
				return $html;
			}

			$childrenHtml = '';
			foreach ($node->getChildren() as $child) {
				$childrenHtml .= $this->_renderNode($child);
			}
			$level = $node->getLevel();
			$html .= '<li>' . $this->_wrapUl($childrenHtml, $level + 1) . '</li>';
			return $html;
		}

		protected abstract function _renderCore(\Saito\Posting\PostingInterface $node);

		/**
		 * Wraps li tags with ul tag
		 *
		 * @param string $string li html list
		 * @param $level
		 * @param $id
		 * @return string
		 */
		protected function _wrapUl($string, $level = null, $id = null) {
			if ($level >= $this->_settings['maxThreadDepthIndent']) {
				return $string;
			}

			$class = 'threadTree-node';
			$data = '';
			if ($level === 0) {
				$class .= ' root';
				$data = 'data-id="' . $id . '"';
			}
			return "<ul {$data} class=\"{$class}\">{$string}</ul>";
		}

		/**
		 * generates CSS classes
		 *
		 * @param $node
		 * @return string
		 */
		protected function _css($node) {
			$entryType = ($node->isRoot()) ? 'et-root' : 'et-reply';
			$entryType .= ($node->isNew()) ? ' et-new' : ' et-old';
			if ($node->get('id') === (int)$this->_settings['currentEntry']) {
				$entryType .= ' et-current';
			}
			$css = $entryType;
			if ($this->_settings['ignore'] && $node->isIgnored()) {
				$css .= ' ignored';
			}
			return $css;
		}

	}
