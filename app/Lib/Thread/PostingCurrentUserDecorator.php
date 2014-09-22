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

	}
