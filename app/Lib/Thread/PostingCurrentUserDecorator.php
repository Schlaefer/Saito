<?php

	App::uses('PostingDecorator', 'Lib/Thread');

	class PostingCurrentUserDecorator extends PostingDecorator {

		protected $_CU;

		public function setCurrentUser($CU) {
			$this->_CU = $CU;
		}

		public function isIgnored() {
			return $this->_CU->ignores($this->getRaw()['Entry']['user_id']);
		}

		public function isNew() {
			$data = $this->getRaw();
			return !$this->_CU->ReadEntries->isRead($data['Entry']['id'], $data['Entry']['time']);
		}

		/**
		 * Checks if posting has newer answers
		 *
		 * currently only supported for root postings
		 *
		 * @return bool
		 * @throws RuntimeException
		 */
		public function hasNewAnswers() {
			if (!$this->isRoot()) {
				throw new RuntimeException('Posting with id ' . $this->id .' is no root posting.');
			}
			if (!isset($this->_CU['last_refresh'])) {
				return false;
			}
			return $this->_CU['last_refresh_unix'] < strtotime($this->last_answer);
		}

	}
