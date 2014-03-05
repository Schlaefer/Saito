<?php

	class SaitoCurrentUserReadEntries {

		protected $_CU;

		protected $_readEntries = null;

		protected $_UserRead;

		protected $_minPostingsToKeep;

		public function __construct(CurrentUserComponent $CurrentUser) {
			$this->_CU = $CurrentUser;
			$this->_UserRead = $CurrentUser->getModel()->UserRead;
		}

		/**
		 *
		 *
		 * @return int
		 * @throws UnexpectedValueException
		 */
		protected function _minNPostingsToKeep() {
			if ($this->_minPostingsToKeep) {
				return $this->_minPostingsToKeep;
			}
			$threadsOnPage = Configure::read('Saito.Settings.topics_per_page');
			$postingsPerThread = Configure::read('Saito.Globals.postingsPerThread');
			$pagesToCache = 1.5;
			$this->_minPostingsToKeep = intval($postingsPerThread * $threadsOnPage * $pagesToCache);
			if (empty($this->_minPostingsToKeep)) {
				throw new UnexpectedValueException();
			}
			return $this->_minPostingsToKeep;
		}

		/**
		 * removes old-data from non-active users
		 */
		public function gcGlobal() {
			$lastEntry = $this->_CU->getModel()->Entry->find('first',
					[
							'contain' => false,
							'fields' => ['Entry.id'],
							'order' => ['Entry.id' => 'DESC']
					]);
			if (!$lastEntry) {
				return;
			}
			$Category = ClassRegistry::init('Category');
			$nCategories = $Category->find('count');
			$entriesToKeep = $nCategories * $this->_minNPostingsToKeep();
			$lastEntryId = $lastEntry['Entry']['id'] - $entriesToKeep;
			$this->_UserRead->deleteEntriesBefore($lastEntryId);
		}

		/**
		 * removes old-data from active users
		 */
		public function gcUser() {
			if (!$this->_CU->isLoggedIn()) {
				return;
			}

			$entries = $this->get();
			$numberOfEntries = count($entries);
			if ($numberOfEntries === 0) {
				return;
			}

			$maxEntriesToKeep = $this->_minNPostingsToKeep();
			if ($numberOfEntries <= $maxEntriesToKeep) {
				return;
			}

			$youngestDeletedEntryId = array_shift(array_slice($entries,
					$numberOfEntries - $maxEntriesToKeep,
					1));
			$this->_UserRead->deleteUserEntriesBefore($this->_id(),
					$youngestDeletedEntryId);

			// all entries older than (and including) the deleted entries become
			// old entries by updating the MAR-timestamp
			$youngestDeletedEntry = $this->_CU->getModel()->Entry->find('first',
					[
							'contain' => false,
							'conditions' => ['Entry.id' => $youngestDeletedEntryId],
							'fields' => ['Entry.time']
					]);
			// can't use  $this->_CU->LastRefresh->set() because this would also
			// delete all of this user's UserRead entries
			$this->_CU->getModel()
					->setLastRefresh($youngestDeletedEntry['Entry']['time']);
		}

		public function get() {
			if ($this->_readEntries === null) {
				$this->_readEntries = $this->_UserRead->getUser($this->_id());
			}
			return $this->_readEntries;
		}

		public function delete() {
			$this->_UserRead->deleteAllFromUser($this->_id());
		}

		/**
		 * Sets single entry as read
		 *
		 * @param $entries array single ['Entry' => []] or multiple [0 => ['Entry' => …]
		 * @throws InvalidArgumentException
		 */
		public function set($entries) {
			Stopwatch::start('SaitoCurrentUserReadEntries::set()');
			if (!$this->_CU->isLoggedIn()) {
				return;
			}

			if (isset($entries['Entry'])) {
				$entries = [0 => $entries];
			}

			if (empty($entries)) {
				throw new InvalidArgumentException;
			}

			// performance: don't store entries covered by timestamp
			foreach ($entries as $k => $entry) {
				if (strtotime($this->_CU['last_refresh']) > strtotime($entry['Entry']['time'])) {
					unset($entries[$k]);
				}
			}
			if (empty($entries)) {
				return;
			}

			$this->_UserRead->setEntriesForUser(
					Hash::extract($entries, '{n}.Entry.id'),
					$this->_id());
			Stopwatch::stop('SaitoCurrentUserReadEntries::set()');
		}

		protected function _id() {
			return $this->_CU->getId();
		}

	}
