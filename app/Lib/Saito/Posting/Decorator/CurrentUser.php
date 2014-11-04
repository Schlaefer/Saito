<?php

	namespace Saito\Posting\Decorator;

	class CurrentUser extends DecoratorInterface {

		protected $_cache = [];

		protected $_CU;

		public function setCurrentUser($CU) {
			$this->_CU = $CU;
		}

		public function isIgnored() {
			return $this->_CU->ignores($this->get('user_id'));
		}

		public function isNew() {
			if (isset($this->_cache['isNew'])) {
				$this->_cache['isNew'] = $this->_cache['isNew'];
			} else {
				$id = $this->get('id');
				$time = $this->get('time');
				$this->_cache['isNew'] = !$this->_CU->ReadEntries->isRead($id, $time);
			}
			return $this->_cache['isNew'];
		}

		/**
		 * Checks if posting has newer answers
		 *
		 * currently only supported for root postings
		 *
		 * @return bool
		 * @throws \RuntimeException
		 */
		public function hasNewAnswers() {
			if (!$this->isRoot()) {
				throw new \RuntimeException('Posting with id ' . $this->get('id') . ' is no root posting.');
			}
			if (!isset($this->_CU['last_refresh'])) {
				return false;
			}
			return $this->_CU['last_refresh_unix'] < strtotime($this->get('last_answer'));
		}

	}
