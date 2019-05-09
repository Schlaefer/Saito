<?php

	App::uses('AppModel', 'Model');

	/**
	 * UserRead Model
	 *
	 * @property User $User
	 */
	class UserRead extends AppModel {

		/**
		 * Caches user entries over multiple validations
		 *
		 * Esp. when many rows are set via Mix-view request
		 *
		 * @var array
		 */
		protected $_userCache = null;

		public $actsAs = ['Containable'];

		/**
		 * belongsTo associations
		 *
		 * @var array
		 */
		public $belongsTo = [
				'User' => [
						'className' => 'User',
						'foreignKey' => 'user_id',
						'conditions' => '',
						'fields' => '',
						'order' => ''
				]
		];

		/**
		 * sets $entriesIds as read for user $userId
		 *
		 * @param array $entriesId [3, 4, 34]
		 * @param int $userId
		 */
		public function setEntriesForUser($entriesId, $userId) {
			// filter out duplicates
			$userEntries = $this->getUser($userId);
			$entriesToSave = array_diff($entriesId, $userEntries);

			if (empty($entriesToSave)) {
				return;
			}

			$data = [];
			foreach ($entriesToSave as $entryId) {
				$this->_userCache[$userId][$entryId] = $entryId;
				$data[] = [
						'entry_id' => $entryId,
						'user_id' => $userId
				];
			}
			$this->create();
			$this->saveMany($data);
		}

		/**
		 * gets all read postings of user with id $userId
		 *
		 * @param int $userId
		 * @return array [1 => 1, 3 => 3]
		 */
		public function getUser($userId) {
			if (isset($this->_userCache[$userId])) {
				return $this->_userCache[$userId];
			}

			Stopwatch::start('UserRead::getUser()');
			$readPostings = $this->find('all',
					[
							'conditions' => ['user_id' => $userId],
							'order' => $this->alias . '.entry_id',
							'contain' => false
					]);

			$read = [];
			foreach ($readPostings as $posting) {
				$id = (int)$posting[$this->alias]['entry_id'];
				$read[$id] = $id;
			}
			$this->_userCache[$userId] = $read;
			Stopwatch::stop('UserRead::getUser()');

			return $this->_userCache[$userId];
		}

		/**
		 * deletes entries with lower entry-ID than $entryId
		 *
		 * @param $entryId
		 * @throws InvalidArgumentException
		 */
		public function deleteEntriesBefore($entryId) {
			if (empty($entryId)) {
				throw new InvalidArgumentException;
			}
			$this->_userCache = null;
			$this->deleteAll([$this->alias . '.entry_id <' => $entryId],
					false,
					false);
		}

		/**
		 * deletes entries with lower entry-ID than $entryId from user $userId
		 *
		 * @param $userId
		 * @param $entryId
		 * @throws InvalidArgumentException
		 */
		public function deleteUserEntriesBefore($userId, $entryId) {
			if (empty($userId) || empty($entryId)) {
				throw new InvalidArgumentException;
			}
			$this->_userCache = null;
			$this->deleteAll([
							$this->alias . '.entry_id <' => $entryId,
							$this->alias . '.user_id' => $userId
					],
					false,
					false);
		}

		/**
		 * deletes entries from user $userId
		 *
		 * @param $userId
		 * @throws InvalidArgumentException
		 */
		public function deleteAllFromUser($userId) {
			if (empty($userId)) {
				throw new InvalidArgumentException;
			}
			$this->_userCache = null;
			$this->deleteAll(['user_id' => $userId], false, false);
		}

	}
