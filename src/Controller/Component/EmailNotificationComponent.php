<?php

	namespace App\Controller\Component;

	use Cake\Controller\Component;
	use App\Lib\Controller\Component\NotificationComponent;

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
			$this->_entryReply($recipients, $event, 'tid');
		}

		protected function _modelEntryReplyToEntry($event, array $recipients) {
			$this->_entryReply($recipients, $event, 'pid');
		}

		protected function _entryReply($recipients, $event, $idKey) {
			$subject = $event->subject()->find('first',
					[
							'contain' => ['User'],
							'conditions' => ['Entry.id' => $event->data['data']['Entry'][$idKey]]
					]);

			$lang = Configure::read('Saito.language');
			$config = [
					'subject' => __('New reply to "%s"', $subject['Entry']['subject']),
					'template' => $lang . DS . 'notification-model-entry-afterReply',
					'viewVars' => [
							'parentEntry' => $subject,
							'newEntry' => $event->data['data'],
					]
			];
			foreach ($recipients as $recipient) {
				if (!$this->_shouldRecipientReceiveReplyMessage($recipient,
						$event->data['data']['Entry'])
				) {
					continue;
				}
				$config['recipient'] = ['User' => $recipient];
				$config['viewVars']['recipient'] = $recipient;
				$config['viewVars']['notification'] = $recipient['Esnotification'];
				$this->_email($config);
			}
			return $config;
		}

		public function userActivatedAdminNotice($event) {
			$recipients = Configure::read('Saito.Notification.userActivatedAdminNoticeToUserWithID');
			if (!is_array($recipients)) {
				return;
			}
			$newUser = $event->data['User'];
			$config = [
				'subject' => __('Successfull registration'),
				'template' => Configure::read(
					'Saito.language'
				) . DS . 'notification-admin-user_activated',
				'viewVars' => array('user' => $newUser, 'ip' => env('REMOTE_ADDR')),

			];
			foreach ($recipients as $recipient) :
				$config['recipient'] = $recipient;
				$this->_email($config);
			endforeach;
		}

		protected function _shouldRecipientReceiveReplyMessage($recipient, $entry) {
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
			$config['sender'] = 'system';
			try {
				$this->_Controller->SaitoEmail->email($config);
			} catch (Exception $e) {
				$Logger = new Saito\Logger\ExceptionLogger();
				$Logger->write('Error in EmailNotificationComponent::_email()', [
					'e' => $e,
					'msgs' => ['config' => $config]
				]);
			}
		}

	}
