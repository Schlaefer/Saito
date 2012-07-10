<?php

	App::uses('CakeEvent', 'Event');
	App::uses('CakeEventListener', 'Event');

	class EmailNotificationComponent extends Component implements CakeEventListener {

		protected $_Controller;
		protected $_Notification;

		public function startup(Controller $controller) {
			parent::startup($controller);
			$this->_Notification = ClassRegistry::init(array( 'class'	 => 'Notification' ));
			CakeEventManager::instance()->attach($this);
			$this->_Controller = $controller;
		}

		public function implementedEvents() {
			return array(
					'Model.Entry.afterReply'	 => 'dispatchEvent',
					'Model.User.afterActivate' => 'userActivatedAdminNotice',
			);
		}

		public function dispatchEvent($event) {
			$events = Configure::read('Saito.Events');

			if ( !isset($events[$event->name()]['EmailNotification']) ) {
				// there are no actions for this event
				return;
			}

			$actions = $this->_Notification->find('all',
					array(
					'contain' => array(
							'User' => array(
									'fields' => array( 'id', 'username', 'user_email' ),
							)
					),
					'conditions' => array(
							'event_id' => array_keys($events[$event->name()]['EmailNotification']),
							'subject'	 => $event->data['subject'],
					)
					));
			if ( $actions ):
				foreach ( $actions as $action ):
					$method = '_' . $events[$event->name()]['EmailNotification'][$action['Notification']['event_id']];
					if ( method_exists($this, $method) ) {
						$this->$method($event, $action['User']);
					}
				endforeach;
			endif;
			return;
		}

		protected function _modelEntryAfterReplyThread($event, array $recipient) {
			// get parent entry
			$event->subject()->contain();
			$rootEntry = $event->subject()->findById($event->data['data']['Entry']['tid']);
			try {
				$this->_Controller->email(array(
						'recipient' => array( 'User'		 => $recipient ),
						'subject'	 => __('New reply to "%s"', $rootEntry['Entry']['subject']),
						'sender'	 => array(
								'User' => array(
										'user_email' => Configure::read('Saito.Settings.forum_email'),
										'username'	 => Configure::read('Saito.Settings.forum_name') ),
						),
						'template'	 => Configure::read('Config.language') . DS . 'notification-model-entry-afterReply',
						'viewVars'	 => array(
								'recipient'		 => $recipient,
								'parentEntry'	 => $rootEntry,
								'newEntry'		 => $event->data['data'],
						),
				));
			} catch ( Exception $exc ) {

			}
		}

		protected function _modelEntryAfterReply($event, array $recipient) {
			// get parent entry
			$event->subject()->contain();
			$parentEntry = $event->subject()->findById($event->data['data']['Entry']['pid']);
			try {
				$this->_Controller->email(array(
						'recipient' => array( 'User'		 => $recipient ),
						'subject'	 => __('New reply to "%s"', $parentEntry['Entry']['subject']),
						'sender'	 => array(
								'User' => array(
										'user_email' => Configure::read('Saito.Settings.forum_email'),
										'username'	 => Configure::read('Saito.Settings.forum_name') ),
						),
						'template'	 => Configure::read('Config.language') . DS . 'notification-model-entry-afterReply',
						'viewVars'	 => array(
								'recipient'		 => $recipient,
								'parentEntry'	 => $parentEntry,
								'newEntry'		 => $event->data['data'],
						),
				));
			} catch ( Exception $exc ) {

			}
		}

		public function userActivatedAdminNotice($event) {
			$recipients = Configure::read('Saito.Notification.userActivatedAdminNoticeToUserWithID');
			if ( !is_array($recipients) )
				return;
			$new_user = $event->data['User'];
			foreach ( $recipients as $recipient ) :
				try {
					$this->_Controller->email(array(
							'recipient'	 => $recipient,
							'subject'		 => __('Successfull registration'),
							'sender'		 => array(
									'User' => array(
											'user_email' => Configure::read('Saito.Settings.forum_email'),
											'username'	 => Configure::read('Saito.Settings.forum_name') ),
							),
							'template'	 => Configure::read('Config.language') . DS . 'notification-admin-user_activated',
							'viewVars'	 => array( 'user' => $new_user, 'ip'	 => env('REMOTE_ADDR') ),
					));
				} catch ( Exception $exc ) {
					
				}
			endforeach;
		}

		protected function _debug($event, array $receivers) {
			foreach ( $receivers as $receiver ) {
				debug($receiver);
			}
		}

	}

?>