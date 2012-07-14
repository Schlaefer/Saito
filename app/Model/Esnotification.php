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
}
