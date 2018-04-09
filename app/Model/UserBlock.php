<?php

	App::uses('AppModel', 'Model');
	App::import('Lib', 'UserBlocker');

	/**
	 * UserBlock Model
	 *
	 * states
	 */
	class UserBlock extends AppModel {

		public $actsAs = [
			'Containable'
		];

		public $belongsTo = [
			'User' => [
				'className' => 'User',
				'fields' => ['id', 'username'],
				'foreignKey' => 'user_id'
			],
			'By' => [
				'className' => 'User',
				'fields' => ['id', 'username'],
				'foreignKey' => 'blocked_by_user_id'
			]
		];

		public $findMethods = [
			'toGc' => true
		];

		public $validate = [
			'ends' => [
				'rule' => ['datetime'],
				'allowEmpty' => true
			]
		];

		public function block($Blocker, $userId, $options) {
			$Blocker->setUserBlockModel($this);
			$success = $Blocker->block($userId, $options);
			if ($success) {
				$this->_updateIsBlocked($userId);
			}
			return $success;
		}

		public function getBlockEndsForUser($userId) {
			$block = $this->find('first', [
				'contain' => false,
				'conditions' => ['user_id' => $userId, 'ended' => null],
				'order' => ['ends' => 'asc']
			]);
			return $block['UserBlock']['ends'];
		}

		/**
		 * @param $id
		 * @return bool true if unblocking was successful, false otherwise
		 * @throws RuntimeException
		 * @throws InvalidArgumentException
		 */
		public function unblock($id) {
			$block = $this->find('first', [
				'contain' => false,
				'conditions' => ['id' => $id, 'ended' => null]
			]);
			if (empty($block)) {
				throw new InvalidArgumentException(
					"No active block with id $id found.",
					1420485052
				);
			}
			$success = (bool)$this->save([
				'id' => $id,
				'ended' => bDate(),
				'ends' => null
			]);
			if (!$success) {
				throw new RuntimeException;
			}
			$this->_updateIsBlocked($block[$this->alias]['user_id']);
			return $success;
		}

		/**
		 * Garbage collection
		 *
		 * called hourly from User model
		 */
		public function gc() {
			$expired = $this->find('toGc');
			foreach ($expired as $block) {
				$this->unblock($block[$this->alias]['id']);
			}
		}

		public function getAll() {
			$blocklist = $this->find('all', [
				'contain' => ['By', 'User'],
				'order' => ['UserBlock.id' => 'DESC']
			]);
			$o = [];
			foreach ($blocklist as $k => $b) {
				$o[$k] = $b['UserBlock'];
				$o[$k]['By'] = $b['By'];
				$o[$k]['User'] = $b['User'];
			}
			return $o;
		}

		public function getAllActive() {
			$blocklist = $this->find('all', [
				'contain' => false,
				'conditions' => ['ended' => null],
				'order' => ['UserBlock.id' => 'DESC']
			]);
			return $blocklist;
		}

		protected function _findToGc($state, $query, $results = array()) {
			if ($state === 'before') {
				$query['contain'] = false;
				$query['conditions'] = [
					'ends !=' => null,
					'ends <' => bDate(),
					'ended' => null
				];
				return $query;
			}
			return $results;
		}

		protected function _updateIsBlocked($userId) {
			$blocks = $this->find('first', [
				'contain' => false,
				'conditions' => [
					'ended' => null,
					'user_id' => $userId
				]
			]);
			$this->User->save(['id' => $userId, 'user_lock' => !empty($blocks)]);
		}

	}
