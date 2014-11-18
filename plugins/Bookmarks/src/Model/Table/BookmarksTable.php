<?php

	namespace Bookmarks\Model\Table;

	use Cake\ORM\Table;

	class BookmarksTable extends Table {

		// @todo 3.0
		/*
		public $validate = array(
			'user_id' => array(
				'numeric' => array(
					'rule' => array('validateUniqueBookmark'),
					//'message' => 'Your custom message here',
					//'allowEmpty' => false,
					'required' => false,
					//'last' => false, // Stop validation after this rule
					'on' => 'create',
					// Limit validation to 'create' or 'update' operations
				),
			),
			'entry_id' => array(
				'numeric' => array(
					'rule' => array('validateUniqueBookmark'),
					//'message' => 'Your custom message here',
					//'allowEmpty' => false,
					'required' => false,
					//'last' => false, // Stop validation after this rule
					'on' => 'create',
					// Limit validation to 'create' or 'update' operations
				),
			),
		);
		*/

		public function initialize(array $config) {
			$this->belongsTo('Entries', ['foreignKey' => 'entry_id'])	;
			$this->belongsTo('Users', ['foreignKey' => 'user_id'])	;
		}

		public function validateUniqueBookmark() {
			$fields = array(
				$this->alias . '.user_id' => $this->data['Bookmark']['user_id'],
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
		public function isBookmarked($entryId, $userId) {
			$result = $this->find(
				'first',
				array(
					'contain' => false,
					'conditions' => array(
						$this->alias . '.entry_id' => $entryId,
						$this->alias . '.user_id' => $userId,
					)
				)
			);
			return $result == true;
		}

	}
