<?php

	App::uses('AppModel', 'Model');

	class UserIgnore extends AppModel {

		public $actsAs = [
			'Containable',
			'Cron.Cron' => [
					'removeOld' => [
							'id' => 'UserIgnore.removeOld',
							'due' => 'daily',
					]
			]
		];

		public $belongsTo = [
			'User' => [
				'className' => 'User',
				'counterCache' => true,
				'fields' => ['id', 'username'],
				'foreignKey' => 'blocked_user_id',
				'order' => ['User.username' => 'asc']
			]
		];

		/**
		 * @var int 3 months
		 */
		public $duration = 8035200;

		public function ignore($userId, $blockedId) {
			$exists = $this->_get($userId, $blockedId);
			if ($exists) {
				return;
			}
			$this->create();
			$this->save([
				'user_id' => $userId,
				'blocked_user_id' => $blockedId,
				'timestamp' => bDate()
			]);
		}

		public function unignore($userId, $blockedId) {
			$entry = $this->_get($userId, $blockedId);
			if (empty($entry)) {
				return;
			}
			$this->delete($entry['Ignore']['id']);
		}

		protected function _get($userId, $blockedId) {
			return $this->find('first', [
				'contain' => false,
				'conditions' => ['user_id' => $userId, 'blocked_user_id' => $blockedId]
			]);
		}

		public function ignoredBy($id) {
			return $this->find(
				'all',
				[
					'contain' => ['User'],
					'conditions' => ['user_id' => $id]
				]
			);
		}

		public function deleteUser($userId) {
			$success = $this->deleteAll(['user_id' => $userId], false);
			$success = $success && $this->deleteAll(['blocked_user_id' => $userId], false);
			return $success;
		}

		/**
		 * counts how many users ignore the user with ID $id
		 *
		 * @param $id
		 * @return array
		 */
		public function countIgnored($id) {
			return $this->find(
				'count',
				[
					'contain' => false,
					'conditions' => ['blocked_user_id' => $id]
				]
			);
		}

		public function removeOld() {
			$this->deleteAll([
					'timestamp <' => bDate(time() - $this->duration)
				],
				false);
		}

	}
