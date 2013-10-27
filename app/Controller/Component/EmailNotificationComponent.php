<?php

	App::uses('NotificationComponent', 'Lib/Controller/Component');

	class EmailNotificationComponent extends NotificationComponent {

		protected $_handledEvents = array(
			'Model.Entry.replyToEntry' => 'modelEntryReplyToEntry',
			'Model.Entry.replyToThread' => 'modelEntryReplyToThread',
		);

		public function implementedEvents() {
			$handledEvents = parent::implementedEvents();
			$handledEvents['Model.User.afterActivate'] = 'userActivatedAdminNotice';
			return $handledEvents;
		}

		protected function _process($event) {
			$recipients = $this->_Esevent->getUsersForEventOnSubjectWithReceiver(
				$event->name(),
				$event->data['subject'],
				'EmailNotification');
			if ($recipients):
				$method = '_' . $this->_handledEvents[$event->name()];
				if (method_exists($this, $method)) {
					$this->$method($event, $recipients);
				}
			endif;
		}

		protected function _modelEntryReplyToThread($event, array $recipients) {
			$event->subject()->contain();
			$rootEntry = $event->subject()->findById($event->data['data']['Entry']['tid']);

			$config = [
				'subject' => __(
					'New reply to "%s"',
					$rootEntry['Entry']['subject']
				),
				'sender' => array(
					'User' => array(
						'user_email' => Configure::read('Saito.Settings.forum_email'),
						'username' => Configure::read('Saito.Settings.forum_name')
					),
				),
				'template' => Configure::read(
					'Config.language'
				) . DS . 'notification-model-entry-afterReply',
				'viewVars' => array(
					'parentEntry' => $rootEntry,
					'newEntry' => $event->data['data'],
				),
			];
			foreach ($recipients as $recipient):
				if ($this->_shouldRecipientReceiveReplyMessage($recipient, $event->data['data']['Entry'])) {
					$config['recipient'] = ['User' => $recipient];
					$config['viewVars']['recipient'] = $recipient;
					$config['viewVars']['notification'] = $recipient['Esnotification'];
					$this->_email($config);
				}
			endforeach;
		}

		protected function _modelEntryReplyToEntry($event, array $recipients) {
			$event->subject()->contain();
			$parentEntry = $event->subject()->findById($event->data['data']['Entry']['pid']);
			$config = [
				'subject' => __(
					'New reply to "%s"',
					$parentEntry['Entry']['subject']
				),
				'sender' => array(
					'User' => array(
						'user_email' => Configure::read('Saito.Settings.forum_email'),
						'username' => Configure::read('Saito.Settings.forum_name')
					),
				),
				'template' => Configure::read(
					'Config.language'
				) . DS . 'notification-model-entry-afterReply',
				'viewVars' => array(
					'parentEntry' => $parentEntry,
					'newEntry' => $event->data['data']
				)
			];
			foreach ($recipients as $recipient):
				if ($this->_shouldRecipientReceiveReplyMessage($recipient, $event->data['data']['Entry'])) {
					$config['recipient'] = ['User' => $recipient];
					$config['viewVars']['recipient'] = $recipient;
					$config['viewVars']['notification'] = $recipient['Esnotification'];
					$this->_email($config);
				}
			endforeach;
		}

		public function userActivatedAdminNotice($event) {
			$recipients = Configure::read('Saito.Notification.userActivatedAdminNoticeToUserWithID');
			if (!is_array($recipients)) {
				return;
			}
			$newUser = $event->data['User'];
			$config = [
				'subject' => __('Successfull registration'),
				'sender' => array(
					'User' => array(
						'user_email' => Configure::read('Saito.Settings.forum_email'),
						'username' => Configure::read('Saito.Settings.forum_name')
					),
				),
				'template' => Configure::read(
					'Config.language'
				) . DS . 'notification-admin-user_activated',
				'viewVars' => array('user' => $newUser, 'ip' => env('REMOTE_ADDR')),

			];
			foreach ($recipients as $recipient) :
				$config['recipient'] = $recipient;
				$this->_email($config);
			endforeach;
		}

		protected function _shouldRecipientReceiveReplyMessage($entry, $recipient) {
			if (Configure::read('debug') === 0 &&
					// don't send answer if new entry belongs to the user itself
					(int)$recipient['id'] === (int)$entry['user_id']
			) {
				return false;
			} else {
				return true;
			}
		}

		protected function _email($config) {
			try {
				$this->_Controller->SaitoEmail->email($config);
			} catch (Exception $exc) {
				$this->log(
					sprintf(
						"Error %s in EmailNotificationComponent::_email() with %s",
						$exc,
						print_r($config, true)
					)
				);
			}
		}

		protected function _debug($event, array $receivers) {
			debug($event);
			foreach ($receivers as $receiver) {
				debug($receiver);
			}
		}

	}
