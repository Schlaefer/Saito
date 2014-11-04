<?php

	namespace Saito\Posting\Decorator;

	use Saito\Posting;

	class DecoratorInterface implements Posting\PostingInterface {

		protected $_Posting;

		public function __construct(\Saito\Posting\Posting $Posting) {
			$this->_Posting = $Posting;
			return $this;
		}

		public function __get($var) {
			return $this->_Posting->{$var};
		}

		public function getChildren() {
			return $this->_Posting->getChildren();
		}

		public function getLevel() {
			return $this->_Posting->getLevel();
		}

		public function getRaw() {
			return $this->_Posting->getRaw();
		}

		public function hasAnswers() {
			return $this->_Posting->hasAnswers();
		}

		public function isNt() {
			return $this->_Posting->isNt();
		}

		public function isPinned() {
			return $this->_Posting->isPinned();
		}

		public function isRoot() {
			return $this->_Posting->isRoot();
		}

		public function addDecorator($fct) {
			return $this->_Posting->addDecorator($fct);
		}

	}