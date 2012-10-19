<?php

	App::uses('AppModel', 'Model');

	/**
	 * Bookmark Model
	 *
	 * @property User $User
	 * @property Entry $Entry
	 */
	class Bookmark extends AppModel {

		public $actsAs = array(
				'Containable',
		);

		protected $fieldsToSanitize = array (
			'comment',
		);

		/**
		 * Validation rules
		 *
		 * @var array
		 */
		public $validate = array(
				'user_id' => array(
						'numeric' => array(
								'rule' => array('validateUniqueBookmark'),
								//'message' => 'Your custom message here',
								//'allowEmpty' => false,
								'required' => false,
								//'last' => false, // Stop validation after this rule
								'on'			 => 'create', // Limit validation to 'create' or 'update' operations
						),
				),
				'entry_id' => array(
						'numeric' => array(
								'rule' => array('validateUniqueBookmark'),
								//'message' => 'Your custom message here',
								//'allowEmpty' => false,
								'required' => false,
								//'last' => false, // Stop validation after this rule
								'on'			 => 'create', // Limit validation to 'create' or 'update' operations
						),
				),
		);

		//The Associations below have been created with all possible keys, those that are not needed can be removed

		/**
		 * belongsTo associations
		 *
		 * @var array
		 */
		public $belongsTo = array(
				'User' => array(
						'className'	 => 'User',
						'foreignKey' => 'user_id',
						'conditions' => '',
						'fields'		 => '',
						'order'			 => ''
				),
				'Entry'			 => array(
						'className'	 => 'Entry',
						'foreignKey' => 'entry_id',
						'conditions' => '',
						'fields'		 => '',
						'order'			 => ''
				)
		);

		public function validateUniqueBookmark() {
			$fields = array(
					$this->alias . '.user_id'	 => $this->data['Bookmark']['user_id'],
					$this->alias . '.entry_id' => $this->data['Bookmark']['entry_id'],
			);
			return $this->isUnique($fields, false);
		}

		/**
		 *
		 * @param int $entry_id
		 * @param int $user_id
		 * @return bool
		 */
		public function isBookmarked($entry_id, $user_id) {
			$result = $this->find('first',
					array(
					'contain'		 => false,
					'conditions' => array(
							$this->alias . '.entry_id' => $entry_id,
							$this->alias . '.user_id'	 => $user_id,
					)
					));
			return $result == true;
		}

	}
