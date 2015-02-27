<?php

	App::uses('AppModel', 'Model');

	/**
	 * Esevent Model
	 *
	 * @property Essubject $Essubject
	 * @property Esnotification $Esnotification
	 */
	class Esevent extends AppModel {

		public $actsAs = array('Containable');

		//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasMany associations
 *
 * @var array
 */
		public $hasMany = array(
			'Esnotification' => array(
				'className' => 'Esnotification',
				'foreignKey' => 'esevent_id',
				'dependent' => true,
				'conditions' => '',
				'fields' => '',
				'order' => '',
				'limit' => '',
				'offset' => '',
				'exclusive' => '',
				'finderQuery' => '',
				'counterQuery' => ''
			)
		);

		protected $_eventTypes = array(
			'Model.Entry.replyToEntry' => 1,
			'Model.Entry.replyToThread' => 2
		);

/**
 * Subject types for $eventsTypes
 *
 * @var array
 */
		protected $_subjectTypes = array(
			'entry' => array(1),
			'thread' => array(2),
		);

		protected $_receivers = array(
				'EmailNotification' => 1,
		);

/**
 *
 * @param int $oldSubject
 * @param int $newSubject
 * @param string $subjectType one of the strings in $_subjectTypes
 */
		public function transferSubjectForEventType($oldSubject, $newSubject, $subjectType) {
			$old = $this->find(
				'all',
				array(
					'contain' => array('Esnotification'),
					'conditions' => array(
						'subject' => $oldSubject,
						'event' => $this->_subjectTypes[$subjectType],
					)
				)
			);

			// there are no affected subjects
			if (!$old) {
				return;
			}

			$current = $this->find(
				'all',
				array(
					'contain' => array('Esnotification'),
					'conditions' => array(
						'subject' => $newSubject,
						'event' => $this->_subjectTypes[$subjectType],
					)
				)
			);

			$oldEvents = Hash::combine($old, '{n}.Esevent.event', '{n}');
			$currentEvents = Hash::combine($current, '{n}.Esevent.event', '{n}');

			foreach ($oldEvents as $eventType => $oldEvent) {
				$newData = array();
				$newData['Esnotification'] = $oldEvent['Esnotification'];
				if (isset($currentEvents[$eventType])) {
					$newData['Esevent']['id'] = $currentEvents[$eventType]['Esevent']['id'];
				} else {
					$newData['Esevent']['subject'] = $newSubject;
					$newData['Esevent']['event'] = $eventType;
					$this->create();
				}
				$this->saveAssociated($newData);
			}
			$this->deleteAll(
				array(
					'subject' => $oldSubject,
					'event' => $this->_subjectTypes[$subjectType],
				),
				false
			);
		}

/**
 *
 * @param int $user user-ID
 * @param array $events
 *
 * <pre>
 * 	array(
 * 			1 => array(
 * 					'subject'				=> 345,
 * 					'event' 				=> 'Model.Event.afterReply',
 * 					'receiver' 			=> 'EmailNotification',
 * 					// true to set, false to unset
 * 					'set'						=> [true|false]
 * 			),
 * 	)
 * </pre>
 */
		public function notifyUserOnEvents($user, $events) {
			foreach ($events as $event) {
				$params = array(
					'user_id' => $user,
					'event' => $this->_eventTypes[$event['event']],
					'subject' => $event['subject'],
					'receiver' => $this->_receivers[$event['receiver']],
				);
				if ($event['set']) {
					$this->setNotification($params);
				} else {
					$this->deleteNotification($params);
				}
			}
		}

/**
 *
 * @param array $params
 *
 * <pre>
 * array(
 * 		'user_id' 			=> '1',
 * 		'event' 				=> '2',
 * 		'subject' 			=> '345',
 * 		'receiver'			=> '1',
 *  );
 *
 * @return type
 */
		public function setNotification($params) {
			$isSet = $this->_getEventSet($params);
			$success = true;
			$eventData = [];
			if ($isSet['Esevent']) {
				$eventData = array(
					'id' => $isSet['Esevent']['id'],
				);
			} else {
				$eventData = array(
					'subject' => $params['subject'],
					'event' => $params['event'],
				);
			}
			if (!$isSet['Esnotification']) {
				$data = array(
					'Esevent' => $eventData,
					'Esnotification' => array(
						array(
							'user_id' => $params['user_id'],
							'esreceiver_id' => $params['receiver'],
						),
					),
				);
				$success = $success && $this->saveAssociated($data);
			}
			return $success;
		}

/**
 * Deletes Subject and all its Notifications
 */
		public function deleteSubject($subjectId, $subjectType) {
			// remove all events
			$this->deleteAll(array(
					'subject' => $subjectId,
					'event' => $this->_subjectTypes[$subjectType],
			), true);
		}

		public function deleteNotification($params) {
			extract($params);

			$isSet = $this->_getEventSet($params);
			if ($isSet['Esnotification']) {
				return $this->Esnotification->deleteNotificationWithId($isSet['Esnotification'][0]['id']);
			}
		}

/**
 * Checks if specific notification is set
 *
 * @param type $params
 *
 * <pre>
 * array(
 * 		'user_id' 			=> '1',
 * 		'event' 				=> '2',
 * 		'subject' 			=> '345',
 * 		'receiver'			=> '1',
 *  );
 * </pre>
 *
 * @return mixed array with found set or false otherwise
 */
		protected function _getEventSet($params) {
			$results = $this->find(
				'first',
				array(
					'contain' => array(
						'Esnotification' => array(
							'conditions' => array(
								'user_id' => $params['user_id'],
								'esreceiver_id' => $params['receiver'],
							),
						),
					),
					'conditions' => array(
						'Esevent.subject' => $params['subject'],
						'Esevent.event' => $params['event'],
					)
				)
			);
			return empty($results) ? false : $results;
		}

/**
 *
 * @param type $eventName
 * @param type $subject
 * @param type $receiver
 * @return array
 *
 * <pre>
 *
 *	array(
 *		(int) 0 => array(
 *			'id' => '1',
 *			'username' => 'Alice',
 *			'user_email' => 'alice@example.com',
 *			'Esnotification' => array(
 *				'id' => '1',
 *				'deactivate' => '1234',
 *				'user_id' => '1',
 *				'esevent_id' => '1'
 *			)
 *		),
 *	)
 * </pre>
 */
		public function getUsersForEventOnSubjectWithReceiver($eventName, $subject, $receiver) {
			$recipients = array();
			$results = $this->find(
				'all',
				array(
					'conditions' => array(
						'Esevent.event' => $this->_eventTypes[$eventName],
						'Esevent.subject' => $subject,
					),
					'contain' => array(
						'Esnotification' => array(
							'fields' => array(
								'Esnotification.id',
								'Esnotification.deactivate'
							),
							'conditions' => array(
								'esreceiver_id' => $this->_receivers[$receiver],
							),
							'User' => array(
								'fields' => array('id', 'username', 'user_email'),
							)
						),
					),
				)
			);
			if ($results) {
				$recipients = Hash::map($results, '{n}.Esnotification.{n}', function ($values) {
					$out = $values['User'];
					unset($values['User']);
					$out['Esnotification'] = $values;
					return $out;
				});
			}
			return $recipients;
		}

/**
 *
 * @param int $user user-ID
 * @param array $events Array with events
 *
 * <code>
 * array(
 * 		1 => array(
 * 				'subject' => 345,
 * 				'event' => 'Model.Entry.afterReply',
 * 				'receiver' => 1
 * 		),
 * );
 * </code>
 * @return array array with $events input array elements replace with bool
 *
 *
 * array(
 * 		1 => [true|false],
 * )
 */
		public function checkEventsForUser($user, $events) {
			// Stopwatch::enable(); Stopwatch::start('Event->checkEventsForUser()');

			$subjects = array();
			foreach ($events as $event) {
				$subjects[] = $event['subject'];
			}

			$notis = $this->_getEventsForUserOnSubjects($user, $subjects);
			$out = array();

			foreach ($events as $k => $event) {
				foreach ($notis as $noti) {
					if ($noti['subject'] == $event['subject']
							&& $noti['event'] == $this->_eventTypes[$event['event']]
							&& $noti['receiver'] == $this->_receivers[$event['receiver']]) {
						$out[$k] = true;
						break;
					} else {
						$out[$k] = false;
					}
				}
			}
			// Stopwatch::end('Event->checkEventsForUser()'); debug(Stopwatch::getString());
			return $out;
		}

/**
 *
 * @param type $user
 * @param array $subjects with subjet IDs
 * @return array
 *
 * array(
 *	'user_id'						 => 1,
 *	'esevent_id'				 => 1,
 *	'esnotification_id'	 => 1,
 *	'event'							 => 1,
 *	'subject'						 => 1,
 *	'receiver'					 => 1,
 *
 * )
 */
		protected function _getEventsForUserOnSubjects($user, $subjects) {
			$notis = $this->Esnotification->find(
				'all',
				array(
					'joins' => array(
						array(
							'table' => 'esevents',
							'alias' => 'Eseventa',
							'type' => 'LEFT',
							'conditions' => array(
								'Eseventa.id = Esnotification.esevent_id'
							)
						)
					),
					'conditions' => array(
						'Esnotification.user_id' => $user,
						'Esevent.subject' => $subjects
					),
					'fields' => array(
						'Esnotification.id',
						'Esnotification.user_id',
						'Esnotification.esevent_id',
						'Esnotification.esreceiver_id',
						'Esevent.event',
						'Esevent.subject'
					)
				)
			);
			$out = $notis;
			if ($notis) {
				$out = array();
				foreach ($notis as $noti) {
					$out[] = array(
						'user_id' => $noti['Esnotification']['user_id'],
						'esevent_id' => $noti['Esnotification']['esevent_id'],
						'esnotification_id' => $noti['Esnotification']['id'],
						'event' => $noti['Esevent']['event'],
						'subject' => $noti['Esevent']['subject'],
						'receiver' => $noti['Esnotification']['esreceiver_id'],
					);
				}
			}
			return $out;
		}

	}
