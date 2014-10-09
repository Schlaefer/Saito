<?php

	App::uses('PostingViewTrait', 'Lib/Thread');

	/**
	 * renders posting into an ul-li HTML-list tree
	 *
	 * Check and benchmark on front-page if you perform changes here!
	 */
	abstract class HtmlRendererAbstract {

		use PostingViewTrait;

		protected $_EntryHelper;

		protected $_defaults = ['ignore' => true, 'currentEntry' => null];

		protected $_settings;

		protected $_lastAnswer;

		public function __construct(EntryHHelper $EntryHelper, $options = []) {
			$this->_EntryHelper = $EntryHelper;
			$this->setOptions($options);
		}

		public function render(PostingInterface $node) {
			$this->_lastAnswer = $node->Thread->getLastAnswer();
			$html = $this->_renderNode($node);
			if ($node->getLevel() === 0) {
				$html = $this->_wrapUl($html, 0, $node->id);
			}
			return $html;
		}

		public function setOptions($options) {
			$this->_settings = $options + $this->_defaults;
		}

		protected function _renderNode(PostingInterface $node) {
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

		protected abstract function _renderCore(PostingInterface $node);

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
			$entryType = ($node->getLevel() === 0) ? 'et-root' : 'et-reply';
			$entryType .= ($node->isNew()) ? ' et-new' : ' et-old';
			if ($node->id === (int)$this->_settings['currentEntry']) {
				$entryType .= ' et-current';
			}
			$css = $entryType;
			if ($this->_settings['ignore'] && $node->isIgnored()) {
				$css .= ' ignored';
			}
			return $css;
		}

	}
