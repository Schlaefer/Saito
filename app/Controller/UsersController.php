<?php

	App::uses('AppController', 'Controller');

class UsersController extends AppController {

	public $name = 'Users';
	public $components = array();

	public $helpers = array (
			'Farbtastic',
			'Flattr.Flattr',
			'SimpleCaptcha.SimpleCaptcha',
			'EntryH',
	);

	protected $allowedToEditUserData = false;

	public function login() {

		if ( $this->Auth->login() ):
		// login was successfull

			$this->User->id = $this->Auth->user('id');
			$this->_successfulLogin();

      if ( isset($this->request->data['User']) && is_array($this->request->data['User']) && isset($this->request->data['User']['password']) ):
        $this->User->autoUpdatePassword($this->request->data['User']['password']);
      endif;

			//* setting cookie
			if ( isset($this->request->data['User']['remember_me']) && $this->request->data['User']['remember_me'] ):
				$this->CurrentUser->PersistentCookie->set();
				unset($this->request->data['User']['remember_me']);
			endif;

			//* handling redirect after successfull login
			if ( $this->localReferer('action') === 'login' ) :
				$this->redirect($this->Auth->redirect());
			else :
				$this->redirect($this->referer());
			endif;

		elseif ( !empty($this->request->data) ) :
      $known_error = false;
      if ( isset($this->request->data['User']['username']) ) :
        $this->User->contain();
        $readUser = $this->User->findByUsername($this->request->data['User']['username']);
        if (empty($readUser) === false) :
          $user = new SaitoUser(new ComponentCollection);
          $user->set($readUser['User']);
          if ( $user->isForbidden() ) :
            $known_error = $known_error || true;
            $this->Session->setFlash(__('User %s is locked.', $readUser['User']['username']), 'flash/warning');
          endif;
        endif;
      endif;
      if ( $known_error === false) :
        // Unknown login error
        $this->Session->setFlash(__('auth_loginerror'), 'default', array(), 'auth');
      endif;
		endif;

	} //end login()

	public function logout() {
		if ($this->Auth->user()) {
			$this->CurrentUser->logout();
		}
		$this->redirect('/');
	}

	public function register ($id = null) {
		Stopwatch::start('Entries->register()');

		$this->set('register_success', false);

	 	$this->Auth->logout();

		// user clicked link in confirm mail
		// @td make name arg
		if ($id && isset($this->passedArgs[1])) :
			$this->User->contain('UserOnline');
			$user = $this->User->read(null, $id);
			if ($user["User"]['activate_code'] == $this->passedArgs[1]) :
        $this->User->id = $id;
        if ( $this->User->activate() ) :
          $this->Auth->login($user);
          $this->set('register_success', 'success');
        endif;
			else :
				$this->redirect(array( 'controller' => 'entries', 'action' => 'index'));
      endif;
    endif;

		if (!empty($this->request->data) && !Configure::read('Saito.Settings.tos_enabled')) {
			$this->request->data['User']['tos_confirm'] = true;
		}

		if (!empty($this->request->data) && $this->request->data['User']['tos_confirm']) {
			$this->request->data = $this->_passwordAuthSwitch($this->request->data);

			$this->request->data['User']['activate_code'] = mt_rand(1000000,9999999);
			$this->User->Behaviors->attach('SimpleCaptcha.SimpleCaptcha');
			if ($this->User->register($this->request->data)) {
					$this->request->data['User']['id'] = $this->User->id;

					$this->SaitoEmail->email(array(
						'recipient' => $this->request->data,
						'subject' 	=> __('register_email_subject', Configure::read('Saito.Settings.forum_name')),
						'sender' 		=> array(
								'User' => array(
										'user_email' 	=> Configure::read('Saito.Settings.forum_email'),
										'username'		=> Configure::read('Saito.Settings.forum_name')),
								),
						'template' 	=> 'user_register',
						'viewVars'	=> array('user' => $this->request->data),
					));
					$this->set('register_success', 'email_send');
			} else {
				// 'unswitch' the passwordAuthSwitch to get the error message to the field
				if (isset($this->User->validationErrors['password'])) {
					$this->User->validationErrors['user_password'] = $this->User->validationErrors['password'];
				}
				$this->request->data['User']['tos_confirm'] = false;
			}
		}
		Stopwatch::stop('Entries->register()');
	}

	public function admin_index() {
		$data = $this->User->find('all', array(
				'contain' => false,
				'order' => array(
						'User.username' => 'asc'
				),
			)
		);

		$this->set('users', $data);
	}

	public function index() {
		$this->paginate = array(
				'contain' => 'UserOnline',
				'conditions'	=> array(
						'OR'	=> array(
								'LENGTH(  `UserOnline`.`user_id` ) <' => 11,
								'ISNULL(  `UserOnline`.`user_id` )'		=> '1',
					),
				),
				'limit' => 400,
				'order' => array(
						'UserOnline.logged_in'	 => 'desc',
						'User.username' => 'asc',
				),
		);

		$data = $this->paginate("User");
		$this->set('users', $data);
	}

	public function admin_add() {
		if ( !empty($this->request->data) ) :
			$this->request->data = $this->_passwordAuthSwitch($this->request->data);
			if ( $this->User->register($this->request->data) ):
				$this->Session->setFlash('Nutzer erfolgreich angelegt @lo', 'flash/notice');
				$this->redirect(array( 'action' => 'view', $this->User->id, 'admin' => false ));
			endif;
		endif;
	}

	public function name($id = null) {
		if(!empty($id)) {
			$this->User->contain();
			$viewed_user = $this->User->findByUsername($id);
			if (!empty($viewed_user)) {
				return $this->redirect(
					array(
						'controller' => 'users',
						'action' => 'view',
						$viewed_user['User']['id']
					)
				);
			}
		}

		$this->Session->setFlash(__('Invalid user'), 'flash/error');
		return $this->redirect('/');
	}

	public function view($id = null) {
		if(!empty($id) && !is_numeric($id)) {
			return $this->redirect(
				array(
					'controller' => 'users',
					'action' => 'name',
					$id
				)
			);
		}

		$this->User->id = $id;

		$this->User->contain(array('UserOnline'));
		$viewed_user = $this->User->read();

		if (empty($this->request->data)) {
			if ($id == null || (!($viewed_user))) {
				$this->Session->setFlash(__('Invalid user'), 'flash/error');
				$this->redirect('/');
			}
		}

		$viewed_user['User']["number_of_entries"] = $this->User->numberOfEntries();

		$this->set('lastEntries',
					$this->User->Entry->getRecentEntries(
							array(
							'user_id'	 => $this->User->id,
							'limit'		 => 20,
							), $this->CurrentUser
					));

		$this->set('user', $viewed_user);
	}

	public function edit($id = null) {
		if (!$this->allowedToEditUserData || !$id && empty($this->request->data))
		{ /** no data to find entry or not allowed * */
			$this->Session->setFlash(__('Invalid user'));
			$this->redirect('/');
		}

		// try to save entry
		if (!empty($this->request->data)) {

			$this->User->id = $id;

			if ($this->CurrentUser['user_type'] != 'admin') { /** make shure only admin can edit these fields * */
				# @td refactor this admin fields together with view: don't repeat code
				unset($this->request->data['User']['username']);
				unset($this->request->data['User']['user_email']);
				unset($this->request->data['User']['user_type']);
			}

			if ( $this->CurrentUser['user_type'] == 'mod' || $this->CurrentUser['user_type'] == 'admin' ) {
				unset($this->request->data['User']['new_posting_notify']);
				unset($this->request->data['User']['new_user_notify']);
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
			$this->User->sanitize(false);
			$this->request->data = $this->User->read();
		}
		$this->set('user', $this->request->data);
	}

  public function lock($id = null) {
      if (  (
              $this->CurrentUser->isAdmin() === true
              || ($this->CurrentUser->isMod() === true && Configure::read('Saito.Settings.block_user_ui'))
            ) === false
          ) :
        return $this->redirect('/');
      endif;

      $this->User->contain();
      $readUser = $this->User->findById($id);
      if ( !$readUser ) :
        $this->Session->setFlash(__('User not found.'), 'flash/error');
        return $this->redirect('/');
      endif;

      $editedUser = new SaitoUser(new ComponentCollection());
      $editedUser->set($readUser['User']);

      if ( $id == $this->CurrentUser->getId() ) :
        $this->Session->setFlash(__("You can't lock yourself."), 'flash/error');
      elseif ( $editedUser->isAdmin() ) :
        $this->Session->setFlash(__("You can't lock administrators.", 'flash/error'),
            'flash/error');
      else :
        $this->User->id = $id;
        $status = $this->User->toggle('user_lock');
        if ( $status !== false ) :
          $message = '';
          if ( $status ) :
            $message = __('User %s is locked.', $readUser['User']['username']);
          else :
            $message = __('User %s is unlocked.', $readUser['User']['username']);
          endif;
          $this->Session->setFlash($message, 'flash/notice');
        else :
          $this->Session->setFlash(__("Error while un/locking."),
              'flash/error');
        endif;
      endif;

      $this->redirect(array( 'action' => 'view', $id ));
    }

  public function admin_delete($id = null) {

    $this->User->contain();
    $readUser = $this->User->findById($id);
    if ( !$readUser ) :
      $this->Session->setFlash(__('User not found.'), 'flash/error');
      return $this->redirect('/');
    endif;

   if ( isset($this->request->data['User']['modeDelete']) ) :
      if ( $id == $this->CurrentUser->getId() ) :
        $this->Session->setFlash(__("You can't delete yourself."), 'flash/error');
      elseif ( $id == 1 ) :
        $this->Session->setFlash(__("You can't delete the installation account."), 'flash/error');
      elseif ($this->User->deleteAllExceptEntries($id)) :
        $this->Session->setFlash(__('User %s deleted.', $readUser['User']['username']), 'flash/notice');
        return $this->redirect('/');
      else:
        $this->Session->setFlash(__("Couldn't delete user."), 'flash/error');
      endif;

      return $this->redirect(
                array( 'controller' => 'users', 'action' => 'view', $id )
        );
    endif;

    $this->set('user', $readUser);
  }

	public function changepassword($id = null) {
		if ( $id == null
        || !$this->_checkIfEditingIsAllowed($this->CurrentUser, $id) ) :
			return $this->redirect('/');
	  endif;

		$this->User->id = $id;
		$user = null;

		if (!empty($this->request->data)) :
			$this->request->data = $this->_passwordAuthSwitch($this->request->data);
			$this->User->id = $id;
			$this->User->contain('UserOnline');
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('change_password_success'), 'flash/success');
				return $this->redirect( array('controller'=>'users', 'action'=>'edit', $id));
			} else {
				$this->Session->setFlash(
            __d('nondynamic',
                current(array_pop($this->User->validationErrors))),
            'flash/error');
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
		}

		// anonymous users only contact admin
		if (!$this->CurrentUser->isLoggedIn() && (int)$id !== 0) {
      $this->redirect('/');
		}

		// set recipient
		if ((int)$id === 0) {
			// recipient is forum owner
			$recipient = array(
					'User' => array(
							'username' => Configure::read('Saito.Settings.forum_name'),
							'user_email' => Configure::read('Saito.Settings.forum_email'),
					)
			);
		} else {
			// recipient is forum user
			$this->User->id = $id;
			$this->User->contain();
			$recipient =  $this->User->read();
		}

		// if recipient was not found
		if (!$recipient
				// or user does not allow personal messages
				|| ((int)$id !== 0) && !$recipient['User']['personal_messages']) :
			$this->redirect('/');
		endif;

		if ($this->request->data) :
			// send email

			$validation_error = false;

			// validate and set sender
			if (!$this->CurrentUser->isLoggedIn() && (int)$id === 0) {
				$sender_contact = $this->request->data['Message']['sender_contact'];
				App::uses('Validation', 'Utility');
				if (!Validation::email($sender_contact)) {
					$this->JsData->addAppJsMessage(
						__('error_email_not-valid'),
						array(
							'type'    => 'error',
							'channel' => 'form',
							'element' => '#MessageSenderContact'
						)
					);
					$validation_error = true;
				} else {
					$sender['User'] = array(
							'username'    => '',
							'user_email'  => $sender_contact,
					);
				}
			} else {
				$sender = $this->CurrentUser->getId();
			}

			// validate and set subject
			$subject = rtrim($this->request->data['Message']['subject']);
			if (empty($subject)) {
				$this->JsData->addAppJsMessage(
					__('error_subject_empty'),
					array(
						'type'    => 'error',
						'channel' => 'form',
						'element' => '#MessageSubject'
					)
				);
				$validation_error = true;
			}

		  if($validation_error === false) :
				try {
					$email = array(
							'recipient' => $recipient,
							'sender' 		=> $sender,
							'subject' 	=> $subject,
							'message'		=> $this->request->data['Message']['text'],
							'template'	=> 'user_contact'
							);

					if (isset($this->request->data['Message']['carbon_copy']) && $this->request->data['Message']['carbon_copy']) {
						$email['ccsender'] = true;
					}

					$this->SaitoEmail->email($email);
					$this->Session->setFlash(__('Message was send.'), 'flash/success');
					return $this->redirect('/');
				} catch (Exception $exc) {
					$this->Session->setFlash(__('Message couldn\'t be send! ' . $exc->getMessage()), 'flash/error');
				} // end try
			endif;

			$this->request->data = $this->request->data + $recipient;

		else :
			// show form
			$this->request->data = $recipient;
	  endif;
	}

	public function ajax_toggle($toggle) {
		if(!$this->CurrentUser->isLoggedIn() || !$this->request->is('ajax')) $this->redirect('/');

		$this->autoRender = false;
		$allowed_toggles = array(
				'show_userlist',
				'show_recentposts',
				'show_recententries',
				'show_shoutbox'
		);
		if (in_array($toggle, $allowed_toggles)) {
			#	$this->Session->setFlash('userlist toggled');
			$this->User->id = $this->CurrentUser->getId();
			$new_value = $this->User->toggle($toggle);
			$this->CurrentUser[$toggle] =  $new_value;
		}
		return $toggle;
	}

	public function ajax_set() {
		if(!$this->CurrentUser->isLoggedIn() || !$this->request->is('ajax')) $this->redirect('/');

		$this->autoRender = false;

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
		return $this->redirect($this->referer());
	}

	public function beforeFilter() {
		Stopwatch::start('Users->beforeFilter()');
		parent::beforeFilter();

		$this->Auth->allow('register', 'login', 'contact');

		if ($this->request->action === 'view') {
			$this->_checkIfEditingIsAllowed($this->CurrentUser);
			$this->_initBbcode();
		}
		if ($this->request->action === 'edit') {
			$this->_checkIfEditingIsAllowed($this->CurrentUser);
		}

		Stopwatch::stop('Users->beforeFilter()');
	}

  /**
   *
   * @param SaitoUser $userWhoEdits
   * @param int $userToEditId
   * @return type
   */
	protected function _checkIfEditingIsAllowed(SaitoUser $userWhoEdits, $userToEditId = null) {
    if (is_null($userToEditId) && isset($this->passedArgs[0])) :
      $userToEditId = $this->passedArgs[0];
    endif;

		if (isset($userWhoEdits['id']) && isset($userToEditId)) {
			if (
							$userWhoEdits['id'] == $userToEditId	 #users own_entry
							|| $userWhoEdits['user_type']  == 'admin'	 #user is admin
			) :
				$this->allowedToEditUserData = true;
		  else:
        $this->allowedToEditUserData = false;
      endif;

			$this->set('allowedToEditUserData', $this->allowedToEditUserData);
		}
    return $this->allowedToEditUserData;
	}

	protected function _successfulLogin() {
		$this->User->incrementLogins();
		$this->CurrentUser->refresh();

		$this->User->UserOnline->setOffline(session_id());
	}

	protected function _passwordAuthSwitch($data) {
		$data['User']['password'] = $data['User']['user_password'];
		unset($data['User']['user_password']);
		return $data;
	}

}
?>