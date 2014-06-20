<?php

	abstract class JbbCodeTextVisitor implements \JBBcode\NodeVisitor {

		protected $_disallowedTags = ['code'];

		/**
		 * @var \Helper calling CakePHP helper
		 */
		protected $_sHelper;

		protected $_sOptions = [];

		public function __construct(\Helper $Helper, array $_sOptions) {
			$this->_sOptions = $_sOptions;
			$this->_sHelper = $Helper;
		}

		public function __get($name) {
			if (is_object($this->_sHelper->$name)) {
				return $this->_sHelper->{$name};
			}
		}

		public function visitDocumentElement(\JBBCode\DocumentElement $documentElement) {
			foreach ($documentElement->getChildren() as $child) {
				$child->accept($this);
			}
		}

		public function visitTextNode(\JBBCode\TextNode $textNode) {
			$textNode->setValue($this->_processTextNode($textNode->getValue()));
		}

		public function visitElementNode(\JBBCode\ElementNode $elementNode) {
			$tagName = $elementNode->getTagName();
			if (in_array($tagName, $this->_disallowedTags)) {
				return;
			}

			/* We only want to visit text nodes within elements if the element's
			 * code definition allows for its content to be parsed.
			 */
			$isParsedContentNode = $elementNode->getCodeDefinition()->parseContent();
			if (!$isParsedContentNode) {
				return;
			}

			foreach ($elementNode->getChildren() as $child) {
				$child->accept($this);
			}
		}

		protected abstract function _processTextNode($text);

	}