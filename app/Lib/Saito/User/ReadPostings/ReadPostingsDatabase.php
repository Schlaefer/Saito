<?php

	namespace Saito\User\ReadPostings;

	/**
	 * Handles read postings by a server table. Used for logged-in users.
	 */
	class ReadPostingsDatabase extends ReadPostingsAbstract {

		protected $_UserRead;

		protected $_minPostingsToKeep;

		public function __construct(\CurrentUserComponent $CurrentUser) {
			parent::__construct($CurrentUser);
			$this->_UserRead = $this->_CurrentUser->getModel()->UserRead;
			$this->_registerGc($this->_CurrentUser->Cron);
		}

		/**
		 * @throws InvalidArgumentException
		 */
		public function set($entries) {
			\Stopwatch::start('ReadPostingsDatabase::set()');
			if (!$this->_CurrentUser->isLoggedIn()) {
				return;
			}

			$entries = $this->_preparePostings($entries);
			if (empty($entries)) {
				return;
			}

			$this->_UserRead->setEntriesForUser($entries, $this->_id());
			\Stopwatch::stop('ReadPostingsDatabase::set()');
		}

		public function delete() {
			$this->_UserRead->deleteAllFromUser($this->_id());
		}

		/**
		 * calculates user quota of allowed entries in DB
		 *
		 * @return int
		 * @throws \UnexpectedValueException
		 */
		protected function _minNPostingsToKeep() {
			if ($this->_minPostingsToKeep) {
				return $this->_minPostingsToKeep;
			}
			$threadsOnPage = \Configure::read('Saito.Settings.topics_per_page');
			$postingsPerThread = \Configure::read('Saito.Globals.postingsPerThread');
			$pagesToCache = 1.5;
			$this->_minPostingsToKeep = intval($postingsPerThread * $threadsOnPage * $pagesToCache);
			if (empty($this->_minPostingsToKeep)) {
				throw new \UnexpectedValueException();
			}
			return $this->_minPostingsToKeep;
		}

		protected function _registerGc(\CronComponent $Cron) {
			$Cron->addCronJob('ReadUser.' . $this->_id(), 'hourly', [$this, 'gcUser']);
			$Cron->addCronJob('ReadUser.global', 'hourly', [$this, 'gcGlobal']);
		}

		/**
		 * removes old data from non-active users
		 *
		 * should prevent entries of non returning users to stay forever in DB
		 */
		public function gcGlobal() {
			$lastEntry = $this->_CurrentUser->getModel()->Entry->find('first',
					[
							'contain' => false,
							'fields' => ['Entry.id'],
							'order' => ['Entry.id' => 'DESC']
					]);
			if (!$lastEntry) {
				return;
			}
			// @bogus why not getModel()->Entry->Category
			$Category = \ClassRegistry::init('Category');
			$nCategories = $Category->find('count');
			$entriesToKeep = $nCategories * $this->_minNPostingsToKeep();
			$lastEntryId = $lastEntry['Entry']['id'] - $entriesToKeep;
			$this->_UserRead->deleteEntriesBefore($lastEntryId);
		}

		/**
		 * removes old data from current users
		 *
		 * should prevent endless growing of DB if user never clicks the MAR-button
		 */
		public function gcUser() {
			if (!$this->_CurrentUser->isLoggedIn()) {
				return;
			}

			$entries = $this->_get();
			$numberOfEntries = count($entries);
			if ($numberOfEntries === 0) {
				return;
			}

			$maxEntriesToKeep = $this->_minNPostingsToKeep();
			if ($numberOfEntries <= $maxEntriesToKeep) {
				return;
			}

			$entriesToDelete = $numberOfEntries - $maxEntriesToKeep;
			// assign dummy var to prevent Strict notice on reference passing
			$dummy = array_slice($entries, $entriesToDelete, 1);
			$oldestIdToKeep = array_shift($dummy);
			$this->_UserRead->deleteUserEntriesBefore($this->_id(), $oldestIdToKeep);

			// all entries older than (and including) the deleted entries become
			// old entries by updating the MAR-timestamp
			$youngestDeletedEntry = $this->_CurrentUser->getModel()->Entry->find('first',
					[
							'contain' => false,
							'conditions' => ['Entry.id' => $oldestIdToKeep],
							'fields' => ['Entry.time']
					]);
			// can't use  $this->_CU->LastRefresh->set() because this would also
			// delete all of this user's UserRead entries
			$this->_CurrentUser->getModel()
					->setLastRefresh($youngestDeletedEntry['Entry']['time']);
		}

		protected function _get() {
			if ($this->_readPostings !== null) {
				return $this->_readPostings;
			}
			$this->_readPostings = $this->_UserRead->getUser($this->_id());
			return $this->_readPostings;
		}

		protected function _id() {
			return $this->_CurrentUser->getId();
		}

	}
