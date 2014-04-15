<?php

	App::uses('AppController', 'Controller');

	class UsersController extends AppController {

		public $name = 'Users';

		public $helpers = [
			'Farbtastic',
			'Flattr.Flattr',
			'SimpleCaptcha.SimpleCaptcha',
			'EntryH',
			'Map',
			'Text'
		];

		public function login() {
			if ($this->CurrentUser->login()):
				if ($this->localReferer('action') === 'login'):
					$this->redirect($this->Auth->redirectUrl());
				else:
					$this->redirect($this->referer());
				endif;
			elseif (empty($this->request->data['User']['username']) === false):
				$unknownError = true;
				$this->User->contain();
				$readUser = $this->User->findByUsername(
					$this->request->data['User']['username']
				);
				if (empty($readUser) === false):
					$user = new SaitoUser(new ComponentCollection);
					$user->set($readUser['User']);
					if ($user->isForbidden()) :
						$unknownError = false;
						$this->Session->setFlash(
							__('User %s is locked.', $readUser['User']['username']),
							'flash/warning'
						);
					endif;
				endif;
				if ($unknownError === true):
					$this->Session->setFlash(__('auth_loginerror'), 'default', [], 'auth');
				endif;
			endif;
		}

		public function logout() {
			if ($this->Auth->user()) {
				$this->CurrentUser->logout();
			}
			$this->redirect('/');
		}

		public function register() {
			$this->set('status', 'view');

			$this->Auth->logout();

			$tosRequired = Configure::read('Saito.Settings.tos_enabled');
			$this->set(compact('tosRequired'));

			// display empty form
			if (empty($this->request->data)) {
				return;
			}

			$data = $this->request->data;

			if (!$tosRequired) {
				$data['User']['tos_confirm'] = true;
			}

			$tosConfirmed = $data['User']['tos_confirm'];
			if (!$tosConfirmed) {
				return;
			}

			$data = $this->_passwordAuthSwitch($data);
			$this->User->Behaviors->attach('SimpleCaptcha.SimpleCaptcha');
			$user = $this->User->register($data);

			// registering failed, show form again
			if (!$user) {
				// undo the passwordAuthSwitch() to display error message for the field
				if (isset($this->User->validationErrors['password'])) {
					$this->User->validationErrors['user_password'] = $this->User->validationErrors['password'];
				}
				$data['User']['tos_confirm'] = false;
				$this->request->data = $data;
				return;
			}

			// registered successfully
			try {
				$forumName = Configure::read('Saito.Settings.forum_name');
				$subject = __('register_email_subject', $forumName);
				$this->SaitoEmail->email([
					'recipient' => $data,
					'subject' => $subject,
					'sender' => ['User' => [
						'user_email' => Configure::read('Saito.Settings.forum_email'),
						'username' => Configure::read('Saito.Settings.forum_name')
					]],
					'template' => 'user_register',
					'viewVars' => ['user' => $data]
				]);
			} catch (Exception $e) {
				$this->set('status', 'fail: email');
				return;
			}

			$this->set('status', 'success');
		}

		/**
		 * register success (user clicked link in confirm mail)
		 *
		 * @param $id
		 * @throws BadRequestException
		 */
		public function rs($id = null) {
			if (!$id) {
				throw new BadRequestException();
			}

			$code = $this->request->query('c');

			try {
				$activated = $this->User->activate((int)$id, $code);
			} catch (Exception $e) {
				$activated = false;
			}

			if (!$activated) {
				$activated = ['status' => 'fail'];
			}
			$this->set('status', $activated['status']);
		}

		public function admin_index() {
			$data = $this->User->find(
					'all',
					[
							'contain' => false,
							'fields' => [
									'id',
									'username',
									'user_type',
									'user_email',
									'registered',
									'user_lock'
							],
							'order' => ['User.username' => 'asc']
					]
			);
			$this->set('users', $data);
		}

		public function index() {
			$this->paginate = [
				'contain' => 'UserOnline',
				'conditions' => [
					'OR' => [
						'LENGTH(  `UserOnline`.`user_id` ) <' => 11,
						'ISNULL(  `UserOnline`.`user_id` )' => '1'
					],
				],
				'limit' => 400,
				'order' => [
					'UserOnline.logged_in' => 'desc',
					'User.username' => 'asc'
				]
			];

			$data = $this->paginate("User");
			$this->set('users', $data);
		}

		public function admin_add() {
			if (!empty($this->request->data)) :
				$this->request->data = $this->_passwordAuthSwitch($this->request->data);
				if ($this->User->register($this->request->data, true)) {
					$this->Session->setFlash(__('user.admin.add.success'),
							'flash/success');
					$this->redirect(['action' => 'view', $this->User->id, 'admin' => false]);
				}
			endif;
		}

		public function map() {
			if (!Configure::read('Saito.Settings.map_enabled')) {
				$this->Session->setFlash(__('admin.setting.disabled', __('admin.feat.map')), 'flash/error');
				$this->redirect('/');
				return;
			}
			$users = $this->User->find('all',
					[
							'contain' => false,
							'conditions' => ['user_place_lat !=' => null],
							'fields' => [
									'User.id',
									'User.username',
									'User.user_place_lat',
									'User.user_place_lng'
							]
					]
			);
			$this->set(compact('users'));
		}

		public function name($id = null) {
			if (!empty($id)) {
				$this->User->contain();
				$viewedUser = $this->User->findByUsername($id);
				if (!empty($viewedUser)) {
					$this->redirect(
						[
							'controller' => 'users',
							'action' => 'view',
							$viewedUser['User']['id']
						]
					);
					return;
				}
			}
			$this->Session->setFlash(__('Invalid user'), 'flash/error');
			$this->redirect('/');
		}

		public function view($id = null) {
			// redirect view/<username> to name/<username>
			if (!empty($id) && !is_numeric($id)) {
				$this->redirect(
					[
						'controller' => 'users',
						'action' => 'name',
						$id
					]
				);
				return; // test case return
			}

			$this->User->id = $id;
			$this->User->contain(['UserOnline']);
			$viewedUser = $this->User->read();

			if ($id === null || empty($viewedUser)) {
				$this->Session->setFlash(__('Invalid user'), 'flash/error');
				$this->redirect('/');
				return;
			}

			$this->initBbcode();
			$viewedUser['User']['number_of_entries'] = $this->User->numberOfEntries();

			$entriesShownOnPage = 20;
			$this->set(
				'lastEntries',
				$this->User->Entry->getRecentEntries(
					$this->CurrentUser,
					[
						'user_id' => $this->User->id,
						'limit' => $entriesShownOnPage
					]
				)
			);

			$this->set(
				'hasMoreEntriesThanShownOnPage',
					($viewedUser['User']['number_of_entries'] - $entriesShownOnPage) > 0
			);

			$viewedUser['User']['solves_count'] = $this->User->countSolved($this->User->id);
			$this->set('user', $viewedUser);
			$this->set(
					'title_for_layout',
					$viewedUser['User']['username']
			);
		}

	/**
	 * @param null $id
	 * @throws Saito\ForbiddenException
	 * @throws BadRequestException
	 */
	public function edit($id = null) {
		if (!$id) {
			throw new BadRequestException;
		}
		if (!$this->_isEditingAllowed($this->CurrentUser, $id)) {
			throw new \Saito\ForbiddenException("Attempt to edit user $id.", [
				'CurrentUser' => $this->CurrentUser,
				'Request' => $this->request
			]);
		}

		// try to save entry
		if (!empty($this->request->data)) {

			$this->User->id = $id;

			if ($this->CurrentUser['user_type'] !== 'admin') {
				//* make shure only admin can edit these fields
				# @td refactor this admin fields together with view: don't repeat code
				unset($this->request->data['User']['username']);
				unset($this->request->data['User']['user_email']);
				unset($this->request->data['User']['user_type']);
			}

			if ($this->User->save($this->request->data)) {
				// save operation was successfull

				// if someone updates *his own* profile update settings for the session
				if ( $this->User->id == $this->CurrentUser->getId() ):
					// because we replace Auth.User we read the whole record again
					// for maybe empty fields such as username, user_email
					// @td recheck, probably not necessary after last [ref] of CurrentUser
					$this->User->contain();
					$this->request->data = $this->User->read();
					$this->CurrentUser->refresh();
				endif;
				$this->redirect(array('action' => 'view', $id));
			} else {
				// save operation failed

				# we possibly don't have username, user_type etc. in this->data on validation error
				# so we read old entry and merge with new data send by user
				$this->User->contain();
				$user = $this->User->read();
				$this->request->data['User'] = array_merge($user['User'], $this->request->data['User']);
				$this->User->set($this->request->data);
				$this->User->validates();
				$this->JsData->addAppJsMessage(
					__('The user could not be saved. Please, try again.'),
					array(
						'type' => 'error'
					)
				);
			}
		}

		if (empty($this->request->data)) {
			//* View Entry by id
			$this->User->id = $id;
			$this->User->contain('UserOnline');
			$this->set('availableThemes',
					array_combine($this->Themes->getAvailable(),
							$this->Themes->getAvailable()));
			$this->request->data = $this->User->read();
		}
		$this->set('user', $this->request->data);
		$this->set(
				'title_for_layout',
				__('Edit %s Profil',
						Properize::prop($this->request->data['User']['username']))
		);
	}

		/**
		 * @param null $id
		 * @throws BadRequestException
		 */
		public function lock($id = null) {
			if (!$id) {
				throw new BadRequestException;
			}

			if (!($this->CurrentUser->isAdmin() || $this->viewVars['modLocking'])) {
				$this->redirect('/');
				return;
			}

			$this->User->contain();
			$readUser = $this->User->findById($id);
			if (!$readUser) {
				$this->Session->setFlash(__('User not found.'), 'flash/error');
				$this->redirect('/');
				return;
			}

			$editedUser = new SaitoUser(new ComponentCollection());
			$editedUser->set($readUser['User']);

			if ($id == $this->CurrentUser->getId()) {
				$this->Session->setFlash(__("You can't lock yourself."), 'flash/error');
			} elseif ($editedUser->isAdmin()) {
				$this->Session->setFlash(
					__("You can't lock administrators.", 'flash/error'),
					'flash/error'
				);
			} else {
				$this->User->id = $id;
				$status = $this->User->toggle('user_lock');
				if ($status !== false) {
					if ($status) {
						$message = __('User %s is locked.', $readUser['User']['username']);
					} else {
						$message = __(
							'User %s is unlocked.',
							$readUser['User']['username']
						);
					}
					$this->Session->setFlash($message, 'flash/success');
				} else {
					$this->Session->setFlash(__("Error while un/locking."), 'flash/error');
				}
			}
			$this->redirect(['action' => 'view', $id]);
		}

		public function admin_delete($id = null) {
			$this->User->contain();
			$readUser = $this->User->findById($id);
			if (!$readUser) {
				$this->Session->setFlash(__('User not found.'), 'flash/error');
				$this->redirect('/');
				return;
			}

			if (isset($this->request->data['User']['modeDelete'])) {
				if ($id == $this->CurrentUser->getId()) {
					$this->Session->setFlash(__("You can't delete yourself."), 'flash/error');
				} elseif ($id == 1) {
					$this->Session->setFlash(__("You can't delete the installation account."), 'flash/error');
				} elseif ($this->User->deleteAllExceptEntries($id)) {
					$this->Session->setFlash(__('User %s deleted.', $readUser['User']['username']), 'flash/success');
					$this->redirect('/');
					return;
				} else {
					$this->Session->setFlash(__("Couldn't delete user."), 'flash/error');
				}
				$this->redirect(['controller' => 'users', 'action' => 'view', $id]);
				return;
			}
			$this->set('user', $readUser);
		}

	public function changepassword($id = null) {
		if ($id == null ||
				!$this->_isEditingAllowed($this->CurrentUser, $id)
		) :
			$this->redirect('/');
			return;
		endif;

		$this->User->id = $id;
		$user = null;

		if (!empty($this->request->data)) :
			$this->request->data = $this->_passwordAuthSwitch($this->request->data);
			$this->User->id = $id;
			$this->User->contain('UserOnline');
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('change_password_success'), 'flash/success');
				$this->redirect(['controller' => 'users', 'action' => 'edit', $id]);
				return;
			} else {
				$this->Session->setFlash(
					__d(
						'nondynamic',
						current(array_pop($this->User->validationErrors))
					),
					'flash/error'
				);
			}
		endif;

		// we have to fill it for the form magic to work
		$this->User->contain("UserOnline");
		$user = $this->User->read();
		$user['User']['password'] = '';
		$this->request->data = $user;
	}

		public function contact($id = null) {
			if ($id === null) {
				$this->redirect('/');
				return;
			}

			// anonymous users only contact admin
			if (!$this->CurrentUser->isLoggedIn() && (int)$id !== 0) {
				$this->redirect('/');
				return;
			}

			// set recipient
			if ((int)$id === 0) {
				// recipient is forum owner
				$recipient = [
					'User' => [
						'username' => Configure::read('Saito.Settings.forum_name'),
						'user_email' => Configure::read('Saito.Settings.forum_email')
					]
				];
			} else {
				// recipient is forum user
				$this->User->id = $id;
				$this->User->contain();
				$recipient = $this->User->read();
			}

			// if recipient was not found
			if (!$recipient ||
					// or user does not allow personal messages
					((int)$id !== 0) && !$recipient['User']['personal_messages']
			):
				$this->redirect('/');
				return;
			endif;

			if ($this->request->data):
				// send email

				$validationError = false;

				// validate and set sender
				if (!$this->CurrentUser->isLoggedIn() && (int)$id === 0) {
					$senderContact = $this->request->data['Message']['sender_contact'];
					App::uses('Validation', 'Utility');
					if (!Validation::email($senderContact)) {
						$this->JsData->addAppJsMessage(
							__('error_email_not-valid'),
							[
								'type' => 'error',
								'channel' => 'form',
								'element' => '#MessageSenderContact'
							]
						);
						$validationError = true;
					} else {
						$sender['User'] = [
							'username' => '',
							'user_email' => $senderContact
						];
					}
				} else {
					$sender = $this->CurrentUser->getId();
				}

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

				if ($validationError === false):
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

						$this->SaitoEmail->email($email);
						$this->Session->setFlash(__('Message was send.'), 'flash/success');
						$this->redirect('/');
						return;
					} catch (Exception $exc) {
						$this->Session->setFlash(
							__('Message couldn\'t be send! ' . $exc->getMessage()),
							'flash/error'
						);
					} // end try
				endif;

				$this->request->data = $this->request->data + $recipient;

			else:
				// show form
				$this->request->data = $recipient;
			endif;
		}

		/**
		 * @throws BadRequestException
		 */
		private function __ajaxBeforeFilter() {
			if (!$this->request->is('ajax')) {
				throw new BadRequestException;
			}
			$this->autoRender = false;
		}

		public function ajax_toggle($toggle) {
			$this->__ajaxBeforeFilter();

			$allowedToggles = [
				'show_userlist',
				'show_recentposts',
				'show_recententries',
				'show_shoutbox'
			];
			if (in_array($toggle, $allowedToggles)) {
				$this->User->id = $this->CurrentUser->getId();
				$newValue = $this->User->toggle($toggle);
				$this->CurrentUser[$toggle] = $newValue;
			}
			return $toggle;
		}

		public function ajax_set() {
			$this->__ajaxBeforeFilter();

			if (isset($this->request->data['User']['slidetab_order'])) {
				$out = $this->request->data['User']['slidetab_order'];
				$out = array_filter($out, 'strlen');
				$out = serialize($out);

				$this->User->id = $this->CurrentUser->getId();
				$this->User->saveField('slidetab_order', $out);
				$this->CurrentUser['slidetab_order'] = $out;
			}

			return $this->request->data;
		}

		/**
		 * @param null $id
		 *
		 * @throws ForbiddenException
		 */
		public function setcategory($id = null) {
			if (!$this->CurrentUser->isLoggedIn()) {
				throw new ForbiddenException();
			}
			$this->User->id = $this->CurrentUser->getId();
			if ($id === 'all') {
				$this->User->setCategory('all');
			} elseif (!$id && $this->request->data) {
				$this->User->setCategory($this->request->data['CatChooser']);
			} else {
				$this->User->setCategory($id);
			}
			$this->redirect($this->referer());
		}

		public function beforeFilter() {
			Stopwatch::start('Users->beforeFilter()');
			parent::beforeFilter();

			$this->Auth->allow('contact', 'login', 'register', 'rs');
			$this->set('modLocking',
					$this->CurrentUser->isMod() && Configure::read('Saito.Settings.block_user_ui')
			);

			Stopwatch::stop('Users->beforeFilter()');
		}

		/**
		 * Checks if the current user is allowed to edit user $userId
		 *
		 * @param SaitoUser $CurrentUser
		 * @param int $userId
		 * @return type
		 */
		protected function _isEditingAllowed(SaitoUser $CurrentUser, $userId) {
			if ($CurrentUser->isAdmin()) {
				return true;
			}
			return $CurrentUser->getId() === (int)$userId;
		}

		protected function _passwordAuthSwitch($data) {
			$data['User']['password'] = $data['User']['user_password'];
			unset($data['User']['user_password']);
			return $data;
		}

	}