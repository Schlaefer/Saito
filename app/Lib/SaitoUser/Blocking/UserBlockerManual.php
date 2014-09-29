<?php

	App::uses('UserBlockerAbstract', 'Lib/SaitoUser/Blocking');

	class UserBlockerManual extends UserBlockerAbstract {

		protected $_defaults = [
			// which state to set: block or unblock; null (default): toggle
			'state' => null,
			'adminId' => null,
			'duration' => null
		];

		public function getReason() {
			return 1;
		}

		/**
		 * block user manually
		 *
		 * @param $userId
		 * @param array $options
		 * @return bool
		 * @throws InvalidArgumentException
		 * @throws Exception
		 */
		public function block($userId, array $options = []) {
			$options += $this->_defaults;

			$user = $this->_Model->User->getProfile($userId);
			if (empty($user)) {
				throw new InvalidArgumentException;
			}
			$conditions = [
				'ended' => null,
				'reason' => $this->getReason(),
				'user_id' => $userId
			];
			if ($options['state'] === null) {
				$existing = $this->_Model->find('first',
					['contain' => false, 'conditions' => $conditions]);
				$state = empty($existing);
			}
			if ($state) {
				if ($options['adminId']) {
					$conditions['by'] = $options['adminId'];
				}
				$this->_Model->create();
				if ($options['duration']) {
					$conditions['ends'] = bDate(time() + $options['duration']);
				}
				$success = $this->_Model->save($conditions);
				if (empty($success)) {
					throw new Exception;
				}
			} else {
				$this->_Model->unblock($existing[$this->_Model->alias]['id']);
			}
			return $state;
		}

	}
