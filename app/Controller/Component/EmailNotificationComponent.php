<?php

	App::uses('CakeEvent', 'Event');
	App::uses('CakeEventListener', 'Event');

	class EmailNotificationComponent extends Component implements CakeEventListener {

		protected $_Controller;
		protected $_Esevent;
		protected $_handledEvents = array(
				'Model.Entry.replyToEntry'	 => 'modelEntryReplyToEntry',
				'Model.Entry.replyToThread'	 => 'modelEntryReplyToThread',
		);

		public function startup(Controller $controller) {
			parent::startup($controller);
			$this->_Esevent = ClassRegistry::init(array('class'						 => 'Esevent'));
			CakeEventManager::instance()->attach($this);
			$this->_Controller = $controller;
		}

		public function implementedEvents() {
			$handledEvents = array_fill_keys(array_keys($this->_handledEvents),
					'dispatchEvent');
			$handledEvents['Model.User.afterActivate'] = 'userActivatedAdminNotice';
			return $handledEvents;
		}

		public function dispatchEvent($event) {
			$recipients = $this->_Esevent->getUsersForEventOnSubjectWithReceiver(
					$event->name(), $event->data['subject'], 'EmailNotification');
			if ($recipients):
				$method = '_' . $this->_handledEvents[$event->name()];
				if ( method_exists($this, $method) ) {
					$this->$method($event, $recipients);
				}
			endif;
			return;
		}

		protected function _modelEntryReplyToThread($event, array $recipients) {
			// get parent entry
			foreach ( $recipients as $recipient ):
				// don't send answer if new entry belongs to the user itself
				if ( Configure::read('debug') === 0 && (int)$recipient['id'] === (int)$event->data['data']['Entry']['user_id'] ) {
					continue;
				}
				$event->subject()->contain();
				$rootEntry = $event->subject()->findById($event->data['data']['Entry']['tid']);
				try {
					$this->_Controller->email(array(
							'recipient' => array('User'		 => $recipient),
							'subject'	 => __('New reply to "%s"', $rootEntry['Entry']['subject']),
							'sender'	 => array(
									'User' => array(
											'user_email' => Configure::read('Saito.Settings.forum_email'),
											'username'	 => Configure::read('Saito.Settings.forum_name')),
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
			endforeach;
		}

		protected function _modelEntryReplyToEntry($event, array $recipients) {
			// get parent entry
			foreach ( $recipients as $recipient ):
				// don't send answer if new entry belongs to the user itself
				if ( Configure::read('debug') === 0 && (int)$recipient['id'] === (int)$event->data['data']['Entry']['user_id'] ) {
					continue;
				}
				$event->subject()->contain();
				$parentEntry = $event->subject()->findById($event->data['data']['Entry']['pid']);
				try {
					$this->_Controller->email(array(
							'recipient' => array('User'		 => $recipient),
							'subject'	 => __('New reply to "%s"', $parentEntry['Entry']['subject']),
							'sender'	 => array(
									'User' => array(
											'user_email' => Configure::read('Saito.Settings.forum_email'),
											'username'	 => Configure::read('Saito.Settings.forum_name')),
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
			endforeach;
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
											'username'	 => Configure::read('Saito.Settings.forum_name')),
							),
							'template'	 => Configure::read('Config.language') . DS . 'notification-admin-user_activated',
							'viewVars'	 => array('user' => $new_user, 'ip'	 => env('REMOTE_ADDR')),
					));
				} catch ( Exception $exc ) {
					
				}
			endforeach;
		}

		protected function _debug($event, array $receivers) {
			debug($event);
			foreach ( $receivers as $receiver ) {
				debug($receiver);
			}
		}

	}

?>