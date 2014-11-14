<?php

	App::uses('AppModel', 'Model');

/**
 * Shout Model
 *
 * @property User $User
 */
	class Shout extends AppModel {

/**
 * Display field
 *
 * @var string
 */
		public $displayField = 'text';

		public $actsAs = [
			'Markup',
			'Containable'
		];

		public $virtualFields = [
			'username' => 'User.username'
		];

/**
 * Validation rules
 *
 * @var array
 */
		public $validate = array(
			'text' => array(
				'maxlength' => array(
					'rule' => array('maxlength', 255),
				),
			),
		);

/**
 * belongsTo associations
 *
 * @var array
 */
		public $belongsTo = array(
			'User' => array(
				'className' => 'User',
				'foreignKey' => 'user_id',
				'conditions' => '',
				'fields' => '',
				'order' => ''
			)
		);

		public $maxNumberOfShouts = 10;

		public function findLastId() {
			$out = 0;
			$lastShout = $this->find(
				'list',
				array(
					'contain' => false,
					'fields' => 'id',
					'order' => 'id desc',
					'limit' => 1
				)
			);
			if ($lastShout) {
				$out = (int)current($lastShout);
			}
			return $out;
		}

/**
 * Get all shouts
 *
 * @return array
 */
		public function get() {
			$shouts = $this->find(
				'all',
				[
					'contain' => 'User.username',
					'order' => 'Shout.id DESC'
				]
			);
			return $shouts;
		}

		public function push($data) {
			$data[$this->alias]['time'] = gmdate("Y-m-d H:i:s", time());
			$this->create($data);
			$success = $this->save();

			$count = $this->find('count');
			while ($success && $count > $this->maxNumberOfShouts) {
				$success = $this->shift();
				$count -= 1;
			}
			return $success;
		}

		public function shift() {
			$currentIds = $this->find(
				'list',
				[
					'fields' => 'Shout.id',
					'order' => 'Shout.id ASC'
				]
			);
			$oldestId = current($currentIds);
			return $this->delete($oldestId);
		}

		public function beforeSave($options = []) {
			if (empty($this->data[$this->alias]['text']) === false) {
				$this->data[$this->alias]['text'] = $this->prepareMarkup($this->data[$this->alias]['text']);
			}
			return true;
		}

	}
