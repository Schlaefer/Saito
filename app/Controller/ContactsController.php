<?php

	App::uses('AppController', 'Controller');

	class ContactsController extends AppController {

		public $uses = [];

		public function beforeFilter() {
			parent::beforeFilter();
			$this->showDisclaimer = true;
			$this->Auth->allow('owner');
		}

		/**
		 * Contacts forum's owner via contact address
		 */
		public function owner() {
			//* show form
			if (empty($this->request->data)) {
				return;
			}

			$recipient = $this->SaitoEmail->getPredefinedSender('contact');

			if ($this->CurrentUser->isLoggedIn()) {
				$sender = $this->CurrentUser->getId();
			} else {
				$senderContact = $this->request->data['Message']['sender_contact'];

				App::uses('Validation', 'Utility');
				if (!Validation::email($senderContact)) {
					$this->JsData->addAppJsMessage(__('error_email_not-valid'), [
						'type' => 'error',
						'channel' => 'form',
						'element' => '#MessageSenderContact'
					]);
					return;
				}

				$sender['User'] = [
					'username' => '',
					'user_email' => $senderContact
				];
			}

			$this->_contact($recipient, $sender);
		}

		/**
		 * Contacts individual user
		 *
		 * @param null $id
		 * @throws InvalidArgumentException
		 * @throws BadRequestException
		 */
		public function user($id = null) {
			if (empty($id) || !$this->CurrentUser->isLoggedIn()) {
				throw new BadRequestException();
			}

			$this->User->id = $id;
			$this->User->contain();
			$recipient = $this->User->read();

			if (empty($recipient) || !$recipient['User']['personal_messages']) {
				throw new InvalidArgumentException();
			}

			$this->set('title_for_page',
				__('user_contact_title', $recipient['User']['username'])
			);

			//* show form
			if (empty($this->request->data)) {
				$this->request->data = $recipient;
				return;
			}

			$sender = $this->CurrentUser->getId();

			$this->_contact($recipient, $sender);
		}

		protected function _contact($recipient, $sender) {
			$validationError = false;

			// validate and set subject
			$subject = rtrim($this->request->data['Message']['subject']);
			if (empty($subject)) {
				$this->JsData->addAppJsMessage(
					__('error_subject_empty'),
					[
						'type' => 'error',
						'channel' => 'form',
						'element' => '#MessageSubject'
					]
				);
				$validationError = true;
			}

			$this->request->data = $this->request->data + $recipient;

			if ($validationError) {
				return;
			}

			try {
				$email = [
					'recipient' => $recipient,
					'sender' => $sender,
					'subject' => $subject,
					'message' => $this->request->data['Message']['text'],
					'template' => 'user_contact'
				];

				if (isset($this->request->data['Message']['carbon_copy']) && $this->request->data['Message']['carbon_copy']) {
					$email['ccsender'] = true;
				}

				$mail = $this->SaitoEmail->email($email);
				$this->set('email', $mail); // for evaluating send mail in test cases
				$this->Session->setFlash(__('Message was send.'), 'flash/success');
				$this->redirect('/');
				return;
			} catch (Exception $e) {
				$Logger = new Saito\Logger\ExceptionLogger();
				$Logger->write('Contact email failed', ['e' => $e]);

				$this->Session->setFlash(
					__('Message couldn\'t be send! ' . $e->getMessage()),
					'flash/error'
				);
			}
		}

	}
