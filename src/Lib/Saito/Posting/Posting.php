<?php

	namespace Saito\Posting;

	use Saito\Posting\Decorator\PostingTrait;
	use Saito\Posting\Decorator\UserPostingTrait;
    use Saito\User\RemovedSaitoUser;
    use Saito\User\SaitoUser;

    class Posting implements PostingInterface {

		use UserPostingTrait;
		use PostingTrait;

		const ALIAS = 'Entry';

		protected $_children = [];

		protected $_level;

		protected $_rawData;

		protected $_Thread;

		public function __construct(\Saito\User\ForumsUserInterface $CurrentUser, $rawData, array $options = [], $tree = null) {
			// @todo 3.0 remove array layer
			$this->_rawData[self::ALIAS] = $rawData;
			if (isset($rawData['_children'])) {
				$this->_rawData['_children'] = $rawData['_children'];
			}
			// @todo 3.0 remove array layer
			if (isset($rawData['category'])) {
				$this->_rawData['Category'] = $rawData['category'];
				$this->_rawData['User'] = $rawData['user'];
			}

            // @todo change after remove array layer above
            if (empty($this->_rawData['User'])) {
                $this->_rawData['User'] = new RemovedSaitoUser();
            } else {
                $this->_rawData['User'] = new SaitoUser($this->_rawData['User']);
            }

			$this->setCurrentUser($CurrentUser);

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

		public function getAllChildren() {
			$postings = [];
			$this->map(
				function ($node) use (&$postings) {
					$postings[$node->get('id')] = $node;
				},
				false
			);
			return $postings;
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

		public function map(callable $callback, $mapSelf = true, $node = null) {
			if ($node === null) {
				$node = $this;
			}
			if ($mapSelf) {
				$callback($node);
			}
			foreach ($node->getChildren() as $child) {
				$this->map($callback, true, $child);
			}
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
					$this->_children[] = new Posting($this->getCurrentUser(), $child, ['level' => $this->_level + 1], $this->_Thread);
				}
			}
			unset($this->_rawData['_children']);
		}

	}

