<?php

	namespace Saito\Thread;

	/**
	 * Class Thread collection of Postings
	 */
	class Thread {

		protected $_Postings = [];

		protected $_rootId;

		public function add(\Saito\Posting\PostingInterface $posting) {
			$id = $posting->id;
			$this->_Postings[$id] = $posting;

			if ($this->_rootId === null) {
				$this->_rootId = $id;
			} elseif ($id < $this->_rootId) {
				$this->_rootId = $id;
			}
		}

		public function get($id) {
			if ($id === 'root') {
				$id = $this->_rootId;
			}
			return $this->_Postings[$id];
		}

		public function getLastAnswer() {
			return strtotime($this->get('root')->last_answer);
		}

	}
