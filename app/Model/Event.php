<?php
App::uses('AppModel', 'Model');
/**
 * Event Model
 *
 */
class Event extends AppModel {

 	public $actsAs = array('Containable');
	public $displayField = 'display_field';

  /**
   * Event->Notification->User
   *
   * @var int
   */
  public $recursive = 2;

	public $virtualFields = array(
    'display_field' => "CONCAT(Event.sender, ' » ', Event.receiver, '::', Event.action)",
	);

	public $hasMany = array(
		'Notification' => array(
			'className' => 'Notification',
			'foreignKey' => 'event_id',
		),
  );
}
