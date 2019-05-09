<?php

/**
 *  Class UserOnline
 */
	class UserOnline extends AppModel {

		public $name = 'UserOnline';

		public $useTable = 'useronline';

		public $actsAs = ['Containable'];

		public $belongsTo = [
			'User' => [
				'className' => 'User',
				'foreignKey' => 'user_id'
			]
		];

		public $validate = [
			'uuid' => [
				'rule' => 'isUnique',
				'required' => true,
				'allowEmpty' => false
			]
		];

/**
 * Time in seconds until a user is considered offline
 *
 * @var int
 */
		public $timeUntilOffline = 1200;

/**
 * Sets user with `$id` online
 *
 * @param string $id identifier
 * @param boolean $loggedIn user is logged-in
 * @throws InvalidArgumentException
 */
		public function setOnline($id, $loggedIn) {
			if (empty($id)) {
				throw new InvalidArgumentException('Invalid Argument $id in setOnline()');
			}
			if (!is_bool($loggedIn)) {
				throw new InvalidArgumentException('Invalid Argument $logged_in in setOnline()');
			}
			$now = time();

			$id = $this->_getShortendedId($id);
			$data = [
				'UserOnline' => [
					'uuid' => $id,
					'logged_in' => $loggedIn,
					'time' => $now
				]
			];

			if ($loggedIn) {
				$data['UserOnline']['user_id'] = $id;
			}

			$user = $this->find('first', ['conditions' => ['uuid' => $id],
				'recursive' => -1, 'callbacks' => false]);

			if ($user) {
				// only hit database if timestamp is outdated
				if ($user['UserOnline']['time'] < ($now - $this->timeUntilOffline)) {
					$this->id = $user['UserOnline']['id'];
					$this->save($data);
				}
			} else {
				$this->id = null;
				$this->create();
				$this->save($data);
			}

			$this->_deleteOutdated();
		}

		/**
		 * Removes user with uuid `$id` from UserOnline
		 *
		 * @param $id
		 *
		 * @return bool
		 */
		public function setOffline($id) {
			$id = $this->_getShortendedId($id);
			return $this->deleteAll(['UserOnline.uuid' => $id], false);
		}

		public function getLoggedIn() {
			Stopwatch::start('UserOnline->getLoggedIn()');
			$loggedInUsers = $this->find(
				'all',
				[
					'contain' => 'User',
					'conditions' => ['UserOnline.logged_in' => true],
					'fields' => ['User.id', 'User.username', 'User.user_type'],
					'order' => ['LOWER(User.username)' => 'ASC']
				]
			);
			Stopwatch::stop('UserOnline->getLoggedIn()');
			return $loggedInUsers;
		}

/**
 * deletes gone user
 *
 * Gone users are user who are not seen for $time_diff minutes.
 *
 * @param string $timeDiff in minutes
 */
		protected function _deleteOutdated($timeDiff = null) {
			if ($timeDiff === null) {
				$timeDiff = $this->timeUntilOffline;
			}
			$this->deleteAll(['time <' => time() - ($timeDiff)], false);
		}

		protected function _getShortendedId($id) {
			return substr($id, 0, 32);
		}

	}