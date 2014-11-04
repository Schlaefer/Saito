<?php

	namespace Saito\Posting;

	class Posting implements PostingInterface {

		const ALIAS = 'Entry';

		protected $_children = [];

		protected $_level;

		protected $_rawData;

		protected $_Thread;

		public function __construct($rawData, array $options = [], $tree = null) {
			$this->_rawData = $rawData;
			$this->_rawData[self::ALIAS]['id'] = (int)$this->_rawData[self::ALIAS]['id'];
			$this->_rawData[self::ALIAS]['pid'] = (int)$this->_rawData[self::ALIAS]['pid'];
			$this->_rawData[self::ALIAS]['tid'] = (int)$this->_rawData[self::ALIAS]['tid'];

			$options += ['level' => 0];
			$this->_level = $options['level'];

			if (!$tree) {
				$tree = new \Saito\Thread\Thread;
			}
			$this->_Thread = $tree;
			$this->_Thread->add($this);

			$this->_attachChildren();
			return $this;
		}

		/**
		 * @param $var
		 * @return mixed
		 * @throws \InvalidArgumentException
		 */
		public function get($var) {
			switch ($var) {
				case (isset($this->_rawData[self::ALIAS][$var])):
					return $this->_rawData[self::ALIAS][$var];
				case (isset($this->_rawData[$var])):
					return $this->_rawData[$var];
				// if key is set but null
				case (array_key_exists($var, $this->_rawData[self::ALIAS])):
					return $this->_rawData[self::ALIAS][$var];
				default:
					throw new \InvalidArgumentException("Attribute '$var' not found in class Posting.");
			}
		}

		public function getLevel() {
			return $this->_level;
		}

		public function getChildren() {
			return $this->_children;
		}

		public function getRaw() {
			return $this->_rawData;
		}

		public function getThread() {
			return $this->_Thread;
		}

		public function hasAnswers() {
			return count($this->_children) > 0;
		}

		/**
		 * checks if entry is n/t
		 *
		 * @return bool
		 */
		public function isNt() {
			return empty($this->_rawData[self::ALIAS]['text']);
		}

		public function isPinned() {
			return $this->_rawData[self::ALIAS]['fixed'] == true;
		}

		public function isRoot() {
			return $this->_rawData[self::ALIAS]['pid'] === 0;
		}

		public function addDecorator($fct) {
			foreach ($this->_children as $key => $child) {
				$newChild = $fct($child);
				$newChild->addDecorator($fct);
				$this->_children[$key] = $newChild;
			}
			$new = $fct($this);
			// replace decorated object in Thread collection
			$this->_Thread->add($new);
			return $new;
		}

		protected function _attachChildren() {
			if (isset($this->_rawData['_children'])) {
				foreach ($this->_rawData['_children'] as $child) {
					$this->_children[] = new Posting($child, ['level' => $this->_level + 1], $this->_Thread);
				}
			}
			unset($this->_rawData['_children']);
		}

	}

