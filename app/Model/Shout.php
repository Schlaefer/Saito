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

		public $actsAs = array(
			'Containable',
		);

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

		protected $fieldsToSanitize = array(
			 'text',
		);

		public $maxNumberOfShouts = 10;

		public function findLastId() {
			$out = 0;
			$last_shout = $this->find(
				'list',
				array(
					'contain' => false,
					'fields' => 'id',
					'order' => 'id desc',
					'limit' => 1
				)
			);

			if ($last_shout) {
				$out = (int)current($last_shout);
			}

			return $out;
		}

		public function get() {
			return $this->find(
				'all',
				['order' => 'Shout.id DESC']
			);
		}

		public function push($data) {

			$data['Shout']['time'] = gmdate("Y-m-d H:i:s", time());
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

			$current_ids = $this->find('list', array(
					'fields' => 'Shout.id',
					'order' => 'Shout.id ASC'
				));
			$oldest_id = current($current_ids);
			return $this->delete($oldest_id);

		}
	}
