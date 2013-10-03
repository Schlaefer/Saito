<?php

	/**
	 *  Class UserOnline
	 */
	class UserOnline extends AppModel {
		public $name = 'UserOnline';
		public $useTable = 'useronline';
		public $primaryKey = 'user_id';
		public $actsAs = ['Containable'];

		public $belongsTo = [
			'User' => [
				'className'  => 'User',
				'foreignKey' => 'user_id'
			]
		];

		public $validate = [
			'user_id' => [
				'rule'       => 'isUnique',
				'required'   => true,
				'allowEmpty' => false
			]
		];

		/**
		 * Time in seconds until a user is considered offline
		 *
		 * @var int
		 */
		public $timeUntilOffline = 1200;

		public function beforeValidate($options = []) {
			parent::beforeValidate($options);

			// @mlf use created/modified
			$this->data['UserOnline']['time'] = time();
		}

		/**
		 * Sets user with `$id` online
		 *
		 * @param string $id identifier
		 * @param boolean $logged_in user is logged-in
		 * @throws InvalidArgumentException
		 */
		public function setOnline($id, $logged_in) {

			if (empty($id)) {
				throw new InvalidArgumentException('Invalid Argument $id in setOnline()');
			}
			if (!is_bool($logged_in)) {
				throw new InvalidArgumentException('Invalid Argument $logged_in in setOnline()');
			}

			$this->id = $this->_getShortendedId($id);
			$data = [
				'UserOnline' => [
					'user_id'   => $this->id,
					'logged_in' => $logged_in
				]
			];

			$this->contain();
			$user = $this->read();

			if ($user) {
				// only hit database if timestamp is outdated
				if ($user['UserOnline']['time'] < (time() - $this->timeUntilOffline)) {
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
		 * Removes user with `$id` from UserOnline
		 *
		 * @param string $id
		 */
		public function setOffline($id) {
			$this->id = $this->_getShortendedId($id);
			return $this->delete($id, false);
		}

		public function getLoggedIn() {
			Stopwatch::start('UserOnline->getLoggedIn()');
			$loggedInUsers = $this->find(
				'all',
				[
					'contain'    => 'User',
					'conditions' => ['UserOnline.logged_in =' => 1],
					'fields'     => 'User.id, User.username, User.user_type',
					'order'      => 'User.username ASC'
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
		 * @param string $time_diff in minutes
		 */
		protected function _deleteOutdated($time_diff = null) {
			if ($time_diff === null) {
				$time_diff = $this->timeUntilOffline;
			}
			$this->deleteAll(['time <' => time() - ($time_diff)], false);
		}

		protected function _getShortendedId($id) {
			return substr($id, 0, 32);
		}

	}