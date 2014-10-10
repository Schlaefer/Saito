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
			$this->CurrentUser->logOut();

			//# just show form
			if (empty($this->request->data['User']['username'])) {
				return;
			}

			//# successful login with request data
			if ($this->CurrentUser->login()) {
				if ($this->localReferer('action') === 'login') {
					$this->redirect($this->Auth->redirectUrl());
				} else {
					$this->redirect($this->referer());
				}
				return;
			}

			//# error on login
			$this->User->contain();
			$username = $this->request->data['User']['username'];
			$readUser = $this->User->findByUsername($username);

			$status = null;

			if (!empty($readUser)) {
				$User = new SaitoUser($readUser['User']);
				$status = $User->isForbidden();
			}

			switch ($status) {
				case 'locked':
					$message = __('User %s is locked.', $readUser['User']['username']);
					break;
				case 'unactivated':
					$message = __('User %s is not activated yet.', $readUser['User']['username']);
					break;
				default:
					$message = __('auth_loginerror');
			}

			// don't autofill password
			unset($this->request->data['User']['password']);

			$Logger = new \Saito\Logger\ForbiddenLogger();
			$Logger->write("Unsuccessful login for user: $username",
				['msgs' => [$message]]);

			$this->Session->setFlash($message, 'default', [], 'auth');
		}

		public function logout() {
			$this->CurrentUser->logout();
		}

		public function register() {
			$this->set('status', 'view');

			$this->CurrentUser->logout();

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
				$email = $this->SaitoEmail->email([
					'recipient' => $data,
					'subject' => $subject,
					'sender' => 'register',
					'template' => 'user_register',
					'viewVars' => ['user' => $user]
				]);
				// only used in test cases
				$this->set('email', $email);
			} catch (Exception $e) {
				$Logger = new Saito\Logger\ExceptionLogger();
				$Logger->write('Registering email confirmation failed', ['e' => $e]);
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
			$menuItems = [
				'username' => [__('username_marking'), []],
				'user_type' => [__('user_type'), []],
				'UserOnline.logged_in' => [__('userlist_online'), ['direction' => 'desc']],
				'registered' => [__('registered'), ['direction' => 'desc']]
			];
			$showBlocked = Configure::read('Saito.Settings.block_user_ui');
			if ($showBlocked) {
				$menuItems['user_lock'] = [__('user.set.lock.t'), ['direction' => 'desc']];
			}

			$this->paginate = [
				'contain' => 'UserOnline',
				'limit' => 400,
				'order' => ['UserOnline.logged_in' => 'desc', 'User.username' => 'asc']
			];
			$users = $this->paginate('User', null, array_keys($menuItems));

			$this->set(compact('menuItems', 'users'));
		}

		public function ignore($blockedId) {
			$this->_ignore($blockedId, true);
		}

		public function unignore($blockedId) {
			$this->_ignore($blockedId, false);
		}

		protected function _ignore($blockedId, $set) {
			if (!$this->CurrentUser->isLoggedIn() || !is_numeric($blockedId)) {
				throw new BadRequestException();
			}
			$userId = $this->CurrentUser->getId();
			$this->User->id = $userId;
			if (!$this->User->exists($userId) || $userId == $blockedId) {
				throw new BadRequestException();
			}
			if ($set) {
				$this->User->Ignore->ignore($userId, $blockedId);
			} else {
				$this->User->Ignore->unignore($userId, $blockedId);
			}
			$this->redirect($this->referer());
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

			if ($this->CurrentUser->getId() == $id) {
				$viewedUser['User']['ignores'] = $this->User->Ignore->ignoredBy($id);
			}
			$viewedUser['User']['solves_count'] = $this->User->countSolved($id);
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
				'CurrentUser' => $this->CurrentUser
			]);
		}

		$this->set('userId', $id);

		// try to save entry
		if (!empty($this->request->data)) {
			$data = $this->request->data['User'];

			unset($data['id']);
			//# make sure only admin can edit these fields
			if ($this->CurrentUser['user_type'] !== 'admin') {
				// @todo DRY: refactor this admin fields together with view
				unset($data['username'], $data['user_email'], $data['user_type']);
			}

			$this->User->id = $id;
			$success = $this->User->save($data);
			if ($success) {
				$this->redirect(['action' => 'view', $id]);
				return;
			} else {
				// if empty fields are missing from send form read user again
				$this->User->contain();
				$user = $this->User->read();
				$this->request->data['User'] = array_merge($user['User'],
					$this->request->data['User']);

				$this->User->set($this->request->data);
				$this->User->validates();

				$this->JsData->addAppJsMessage(
					__('The user could not be saved. Please, try again.'),
					['type' => 'error']);
			}
		}

		if (empty($this->request->data)) {
			//* View Entry by id
			$this->User->id = $id;
			$this->User->contain('UserOnline');
			$this->request->data = $this->User->read();
		}

		$themes = $this->Themes->getAvailable();
		$this->set('availableThemes', array_combine($themes, $themes));
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

			$editedUser = new SaitoUser($readUser['User']);

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

		/**
		 * changes user password
		 *
		 * @param null $id
		 * @throws Saito\ForbiddenException
		 * @throws BadRequestException
		 */
		public function changepassword($id = null) {
			if (!$id) {
				throw new BadRequestException();
			}

			$user = $this->User->getProfile($id);
			$allowed = $this->_isEditingAllowed($this->CurrentUser, $id);
			if (empty($user) || !$allowed) {
				throw new \Saito\ForbiddenException("Attempt to change password for user $id.",
					['CurrentUser' => $this->CurrentUser]);
			}
			$this->set('userId', $id);
			$this->set('username', $user['username']);

			//# just show empty form
			if (empty($this->request->data)) {
				return;
			}

			//# process submitted form
			$this->request->data = $this->_passwordAuthSwitch($this->request->data);
			$data = [
				'id' => $id,
				'password_old' => $this->request->data['User']['password_old'],
				'password' => $this->request->data['User']['password'],
				'password_confirm' => $this->request->data['User']['password_confirm']
			];
			$success = $this->User->save($data);

			if ($success) {
				$this->Session->setFlash(__('change_password_success'),
					'flash/success');
				$this->redirect(['controller' => 'users', 'action' => 'edit', $id]);
				return;
			}

			$this->Session->setFlash(
				__d('nondynamic', current(array_pop($this->User->validationErrors))),
				'flash/error'
			);

			// unset all autofill form data
			$this->request->data = [];
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

		/**
		 * toggles slidetabs open/close
		 *
		 * @return $this|mixed
		 * @throws BadRequestException
		 */
		public function slidetab_toggle() {
			$this->__ajaxBeforeFilter();

			$toggle = $this->request->data('slidetabKey');
			$allowed = [
				'show_userlist',
				'show_recentposts',
				'show_recententries',
				'show_shoutbox'
			];
			if (!$toggle || !in_array($toggle, $allowed)) {
				throw new BadRequestException(null, 1412949882);
			}

			$this->User->id = $this->CurrentUser->getId();
			$newValue = $this->User->toggle($toggle);
			$this->CurrentUser[$toggle] = $newValue;
			return $toggle;
		}

		/**
		 * sets slidetab-order
		 *
		 * @return bool
		 * @throws BadRequestException
		 */
		public function slidetab_order() {
			$this->__ajaxBeforeFilter();

			$order = $this->request->data('slidetabOrder');
			if (!$order) {
				throw new BadRequestException;
			}

			$allowed = $this->viewVars['slidetabs'];
			$order = array_filter($order, function($item) use ($allowed) {
				return in_array($item, $allowed);
			});
			$order = serialize($order);

			$this->User->id = $this->CurrentUser->getId();
			$this->User->saveField('slidetab_order', $order);
			$this->CurrentUser['slidetab_order'] = $order;

			return true;
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

			// @todo CSRF protection
			$this->Security->unlockedActions[] = 'slidetab_toggle';
			$this->Security->unlockedActions[] = 'slidetab_order';

			$this->Auth->allow('login', 'register', 'rs');
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
		protected function _isEditingAllowed(ForumsUserInterface $CurrentUser, $userId) {
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