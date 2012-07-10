<?php

	App::uses('AppModel', 'Model');

	/**
	 * Notification Model
	 *
	 * @property Event $Event
	 * @property User $User
	 */
	class Notification extends AppModel {

		public $actsAs = array( 'Containable' );

		/**
		 * belongsTo associations
		 *
		 * @var array
		 */
		public $belongsTo = array(
				'Entry' => array(
						'className'	 => 'Event',
						'foreignKey' => 'subject',
				),
				'Event'			 => array(
						'className'	 => 'Event',
						'foreignKey' => 'event_id',
				),
				'User'			 => array(
						'className'	 => 'User',
						'foreignKey' => 'user_id',
				)
		);

		/**
		 *
		 * @param type $data
		 *
		 * <pre>
		 * 	array(
		 * 		'0' => array(
		 * 			'Notification' => array(
		 * 				'eventId' => 1,
		 * 				'userId'	=> 15,
		 * 				'subject' => 54221,
		 * 				'set'		=> [true|false],
		 * 			)
		 * 		)
		 *  )
		 * </pre>
		 *
		 * @param array $mandantoryEvents
		 */
		public function setNotifications($data, $eventFilter = false) {
			foreach($data as $n) {
				$n = $n['Notification'];
				if ($eventFilter && in_array($n['eventId'], $eventFilter) === false) {
					continue;
				}
				if ($n['set']) {
					$this->_setNotification($n['eventId'], $n['userId'], $n['subject']);
				} else {
					$this->_unsetNotification($n['eventId'], $n['userId'], $n['subject']);
				}
			}
		}

		protected function _setNotification($eventId, $userId, $subject) {
			$isSet = $this->find('first',
												array(
					'conditions' => array(
							'event_id' => $eventId,
							'user_id'	 => $userId,
							'subject'	 => $subject,
					)
					)
			);
			$success	 = true;
			if ( !$isSet ) {
				$data = array(
						'event_id' => $eventId,
						'user_id'	 => $userId,
						'subject'	 => $subject,
				);
				$this->create();
				$success	 = $this->save($data);
			}
			return $success;
		}

		public function _unsetNotification($eventId, $userId, $subject) {
			return $this->deleteAll(
							array(
							'event_id' => $eventId,
							'user_id'	 => $userId,
							'subject'	 => $subject,
							), false);
		}

	}

