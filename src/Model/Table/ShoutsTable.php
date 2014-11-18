<?php

	namespace App\Model\Table;

	use Cake\Event\Event;
	use Cake\ORM\Entity;
	use Cake\ORM\Table;

	class ShoutsTable extends Table {

/**
 * Display field
 *
 * @var string
 */
		public $displayField = 'text';

		public $virtualFields = [
			'username' => 'User.username'
		];

/**
 * Validation rules
 *
 * @var array
 */
		// @todo 3.0
		public $validate = array(
			'text' => array(
				'maxlength' => array(
					'rule' => array('maxlength', 255),
				),
			),
		);

		public $maxNumberOfShouts = 10;

		public function initialize(array $config) {
			$this->belongsTo('Users', ['foreignKey' => 'user_id']);

			$this->addBehavior('Markup');
			$this->addBehavior('Timestamp');
		}

		public function findLastId() {
			$last = 0;
			$lastShout = $this->find()
				->select(['id'])
				->order(['id' => 'DESC'])
				->hydrate(false)
				->first();
			if ($lastShout) {
				$last = $lastShout['id'];
			}
			return $last;
		}

		/**
		 * Get all shouts
		 *
		 * @return array
		 */
		public function get($primaryKey = null, $options = []) {
			$shouts = $this->find()
				->contain(['Users' => function($query) {
						return $query->select(['username']);
					}])
				->order(['Shouts.id' => 'DESC'])
				->all();
			return $shouts;
		}

		public function push($data) {
			$data['time'] = bDate();
			$entity = $this->newEntity($data);
			$success = $this->save($entity);

			$count = $this->find()->count();
			while ($success && $count > $this->maxNumberOfShouts) {
				$success = $this->shift();
				$count -= 1;
			}
			return $success;
		}

		public function shift() {
			$currentIds = $this->find()
				->select(['id'])
				->order(['id' => 'ASC'])
				->first();
			return $this->delete($currentIds);
		}

		public function beforeSave(Event $event, Entity $entity, \ArrayObject $options) {
			if ($entity->dirty('text')) {
				$entity->set('text', $this->prepareMarkup($entity->get('text')));
			}
		}

	}
