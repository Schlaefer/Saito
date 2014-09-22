<?php

	App::uses('Thread', 'Lib/Thread');
	App::uses('PostingInterface', 'Lib/Thread');

	class Posting implements PostingInterface {

		public $Thread;

		protected $_children = [];

		protected $_level;

		protected $_rawData;

		public function __construct($rawData, array $options = [], $tree = null) {
			$options += ['level' => 0];
			$this->_rawData = $rawData;
			$this->_level = $options['level'];

			if (!$tree) {
				$tree = new Thread;
			}
			$this->Thread = $tree;
			$this->Thread->add($this);

			$this->_attachChildren();
			return $this;
		}

		/**
		 * magic get accessor
		 *
		 * @param $var
		 * @return int
		 * @throws InvalidArgumentException
		 */
		public function __get($var) {
			switch ($var) {
				case 'id':
					return (int)$this->_rawData['Entry']['id'];
				case 'pid':
					return (int)$this->_rawData['Entry']['pid'];
				case (isset($this->_rawData['Entry'][$var])):
					return $this->_rawData['Entry'][$var];
				default:
					throw new InvalidArgumentException("Attribute '$var' not found in class Posting.");
			}
		}

		public function getChildren() {
			return $this->_children;
		}

		public function getLevel() {
			return $this->_level;
		}

		public function getRaw() {
			return $this->_rawData;
		}

		public function isRoot() {
			return $this->pid === 0;
		}

		public function addDecorator($fct) {
			foreach ($this->_children as $key => $child) {
				$newChild = $fct($child);
				$newChild->addDecorator($fct);
				$this->_children[$key] = $newChild;
			}
			$new = $fct($this);
			$this->Thread->add($new);
			return $new;
		}

		protected function _attachChildren() {
			if (isset($this->_rawData['_children'])) {
				foreach ($this->_rawData['_children'] as $child) {
					$this->_children[] = new Posting($child, ['level' => $this->_level + 1], $this->Thread);
				}
			}
			unset($this->_rawData['_children']);
		}

	}

