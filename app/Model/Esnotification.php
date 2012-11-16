<?php
App::uses('AppModel', 'Model');
/**
 * Esnotification Model
 *
 * @property User $User
 * @property Esevent $Esevent
 */
class Esnotification extends AppModel {


	//The Associations below have been created with all possible keys, those that are not needed can be removed

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
		),
		'Esevent' => array(
			'className' => 'Esevent',
			'foreignKey' => 'esevent_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public function beforeSave($options = array()) {
		if (empty($this->data[$this->alias]['deactivate'])) {
			$this->data[$this->alias]['deactivate'] = mt_rand(0,99999999);
		}

		return parent::beforeSave();
	}

	public function deleteNotificationWithId($id) {
			return $this->delete($id, false);
	}

	public function deleteAllFromUser($userId) {
		return $this->deleteAll(array('user_id' => $userId), false);
	}
}
