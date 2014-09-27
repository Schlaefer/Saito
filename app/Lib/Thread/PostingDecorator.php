<?php

	abstract class PostingDecorator implements PostingInterface {

		protected $_Posting;

		public function __construct(Posting $Posting) {
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

		public function isRoot() {
			return $this->_Posting->isRoot();
		}

		public function addDecorator($fct) {
			return $this->_Posting->addDecorator($fct);
		}

	}