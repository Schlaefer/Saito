<?php

	namespace App\Model\Table;

	use Cake\ORM\Query;
	use Cake\ORM\Table;
	use Cake\Validation\Validator;

	class UserBlocksTable extends Table {

		public function initialize(array $config) {
			$this->belongsTo(
				'Users',
				[
					// @todo 3.0 fields is no longer supported
//					'fields' => ['id', 'username'],
					'foreignKey' => 'user_id'
				]
			);
			$this->belongsTo(
				'By',
				[
					'className' => 'Users',
					// @todo 3.0
//					'fields' => ['id', 'username'],
					'foreignKey' => 'by'
				]
			);
		}

		public function validationDefault(Validator $validator) {
			$validator
				->allowEmpty('ends')
				->add('ends', 'datetime', ['rule' => ['datetime']]);
			return $validator;
		}

		public function block($Blocker, $userId, $options) {
			$Blocker->setUserBlockTable($this);
			$success = $Blocker->block($userId, $options);
			if ($success) {
				$this->_updateIsBlocked($userId);
			}
			return $success;
		}

		public function getBlockEndsForUser($userId) {
			$block = $this->find('all', [
				'conditions' => ['user_id' => $userId, 'ended IS' => null],
				'sort' => ['ends' => 'asc']
			])->first();
			return $block->get('ends');
		}

		/**
		 * @param $id
		 * @throws \RuntimeException
		 * @throws \InvalidArgumentException
		 */
		public function unblock($id) {
			$block = $this->find()->where(['id' => $id, 'ended IS' => null])->first();
			if (!$block) {
				throw new \InvalidArgumentException;
			}
			$this->patchEntity(
				$block,
                ['ended' => bDate(), 'ends' => null]
			);

			if (!$this->save($block)) {
				throw new \RuntimeException(
					"Couldn't unblock block with id $id.",
					1420540471
				);
			}
			$this->_updateIsBlocked($block->get('user_id'));
		}

		/**
		 * Garbage collection
		 *
		 * called hourly from User model
		 */
		public function gc() {
			$expired = $this->find('toGc')->all();
			foreach ($expired as $block) {
				$this->unblock($block->get('id'));
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

		public function findToGc(Query $query, array $options) {
			$query->where(
					[
						'ends IS NOT' => null,
						'ends <' => bDate(),
						'ended IS' => null
					]
				);
			return $query;
		}

		protected function _updateIsBlocked($userId) {
			$blocks = $this->find('all', [
				'conditions' => [
					'ended IS' => null,
					'user_id' => $userId
				]
			])->first();
            $user = $this->Users->get($userId, ['fields' => ['id', 'user_lock']]);
            $user->set('user_lock', !empty($blocks));
            $this->Users->save($user);
		}

	}
