<?php

App::uses('CakeEmail', 'Network/Email');

class UsersController extends AppController {

	public $name = 'Users';
	public $components = array();

	public $helpers = array (
			'Farbtastic',
			'Flattr.Flattr',
			'SimpleCaptcha.SimpleCaptcha',
			'TimeH',
			'EntryH',
	);
	
	protected $allowedToEditUserData = false;

	public function login() {
		$this->set('LocationSubnavLeft', __('login_linkname'));

		if ( $this->Auth->login() ):
		// login was successfull

			$this->User->id = $this->Auth->user('id');
			$this->_successfulLogin();

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

		elseif ( !empty($this->request->data) ):
		// login was attempted but not successfull
			$this->Session->setFlash(__('auth_loginerror'), 'default', array(), 'auth');
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

		# @td make name arg
		if ($id && isset($this->passedArgs[1]))
		{
			$this->User->contain('UserOnline');
			$user = $this->User->read(null, $id);
			if ($user["User"]['activate_code'] == $this->passedArgs[1])
			{
				$this->User->saveField('activate_code', '');
				$this->Auth->login($user);
				$this->set('register_success', 'success');
			}
			else {
				$this->redirect(array( 'controller' => 'entries', 'action' => 'index'));
			}
		}

		if (!empty($this->request->data)) {
			$this->request->data = $this->_passwordAuthSwitch($this->request->data);

			$this->request->data['User']['activate_code'] = mt_rand(1,9999999);
			$this->User->Behaviors->attach('SimpleCaptcha.SimpleCaptcha');
			if ($this->User->register($this->request->data)) {
					$this->request->data['User']['id'] = $this->User->id;

					$this->_email(array(
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
			}
		}

		Stopwatch::stop('Entries->register()');
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
			$this->request->data['User']['activate_code'] = '';

			$this->User->create();
			if ( $this->User->register($this->request->data) ):
				$this->Session->setFlash('Nutzer erfolgreich angelegt @lo', 'flash/notice');
				$this->redirect(array( 'action' => 'view', $this->User->id ));
			endif;
		endif;
	}

	public function view($id = NULL) {
		$this->User->id = $id;

		$this->User->contain(array('UserOnline'));
		$viewed_user = $this->User->read();
		
		if (empty($this->request->data)) {
			if ($id == NULL || (!($viewed_user))) {
				$this->Session->setFlash((__('Invalid user')));
				$this->redirect('/');
			}
		}

		$viewed_user['User']["number_of_entries"] = $this->User->numberOfEntries();

		$this->set('lastEntries', $this->User->Entry->getRecentEntries(array( 'user_id' => $this->User->id) ));

		/** View Entry * */
		$this->set('user', $viewed_user);

		/** set sub_nav_left * */
	  $this->set('headerSubnavLeft', array(
        'title' => '<i class="icon-arrow-left"></i> ' . __('back_to_forum_linkname'),
        'url' => '/'));

	}

	public function edit($id = NULL) {
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

				$this->Session->setFlash(__('user_edit_success'), 'flash/notice');
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
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
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

		/** set sub_nav_left **/
	  $this->set('headerSubnavLeft', array(
        'title' => '<i class="icon-arrow-left"></i> ' . __('Back'),
        'url' => array ( 'controller' => 'users', 'action' => 'view', $this->User->id)));
	}

	public function changepassword($id = null) {
		if ( $id == null || !$this->_checkIfEditingIsAllowed($id) ) {
			$this->redirect('/');
		}

		$this->User->id = $id;
		$user = null;

		if (empty($this->request->data)) {
			# we have to fill it for the form magic to work
			$this->User->contain("UserOnline");
			$user = $this->User->read();
			$user['User']['password'] = '';
			$this->request->data = $user;
		}
		else
		{
			$this->request->data = $this->_passwordAuthSwitch($this->request->data);
			$this->User->id = $id;
			$this->User->contain('UserOnline');
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('change_password_success'), 'flash/notice');
				$this->redirect( array('controller'=>'users', 'action'=>'edit', $id));
			}
			$this->request->data['User']['id'] = $id;
		}

	  $this->set('headerSubnavLeft', array(
        'title' => '<i class="icon-arrow-left"></i> ' . __('Back'),
        'url' => array ( 'controller' => 'users', 'action' => 'edit', $id)));
	}

	public function contact($id = NULL) {
		if ($id === NULL) {
			$this->redirect('/');
		}

		//* anonymous users only contact admin
		if ( !$this->CurrentUser->getId() && $id != 1 ) :
			$this->redirect('/');
		endif;

		$this->User->id = $id;
		$this->User->contain();
		$user =  $this->User->read();
		if (!$user || (!$user['User']['personal_messages'] && $id !=1)) :
			$this->redirect('/');
		endif;

		$send = false;

		if ($this->request->data) {
			$subject = rtrim($this->request->data['Message']['subject']);
			if (empty($subject)) {
				$this->Session->setFlash(__('error_subject_empty'));
				$this->request->data = array_merge($this->request->data, $user);
			} else {
				try {
					$this->_email(array(
							'recipient' => $user,
							'sender' 		=> $this->CurrentUser->getId(),
							'subject' 	=> $subject,
							'message'		=> $this->request->data['Message']['text'],
							'template'	=> 'user_contact'
							));
					$send = true;
					$this->Session->setFlash(__('Message was send.'), 'flash/notice');
						$this->redirect('/');
				} catch (Exception $exc) {
					$this->Session->setFlash(__('Error, message couldn\'t be send!'), 'flash/error');
				} // end try
			} // end if
		} // end if($this->request->data)
		else {
			$this->request->data = $user;
		}

		$this->set('send', $send);
	} // end contact()

	public function ajax_toggle($toggle) {
		if(!$this->CurrentUser->isLoggedIn() || !$this->request->is('ajax')) $this->redirect('/');

		$this->autoRender = false;
		$allowed_toggles = array(
				'show_userlist',
				'show_recentposts',
				'show_about',
				'show_donate',
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

	public function beforeFilter() {
		Stopwatch::start('Users->beforeFilter()');
		parent::beforeFilter();

		$this->Auth->allow('register', 'login', 'contact');

		if ($this->request->action === 'view') {
			$this->_checkIfEditingIsAllowed();
			$this->_loadSmilies();
		}
		if ($this->request->action === 'edit') {
			$this->_checkIfEditingIsAllowed();
		}

		Stopwatch::stop('Users->beforeFilter()');
	}

	/**
	 * @td better mvc. refactor into SaitoUser or overwrite CakeEmail?
	 *
	 * $options = array(
	 * 		'recipient' // user-id or ['User']
	 * 		'sender'		// user-id or ['User']
	 * 		'template'
	 * 		'message'
	 * 		'viewVars'
	 * );
	 *
	 * @param type $options
	 * @throws Exception 
	 */
	protected function _email($options = array()) {
		$defaults = array(
				'viewVars'=> array(),
		);
		extract(array_merge($defaults, $options));

		if (!is_array($recipient)) {
			$this->User->id = $recipient;
			$this->User->contain();
			$recipient = $this->User->read();
			if($recipient == false) {
				throw new Exception('Can\'t find recipient for email.');
			}
		}
		if (!is_array($sender)) {
			$this->User->id = $sender;
			$this->User->contain();
			$sender = $this->User->read();
			if($sender == false) {
				throw new Exception('Can\'t find sender for email.');
			}
		}

		$emailConfig = array(
						'from'	=> array($sender['User']['user_email'] => $sender['User']['username']),
						'to'          => $recipient['User']['user_email'],
						'subject'     => $subject,
						'emailFormat' => 'text',
					);

		if (isset($template)) :
			$emailConfig['template'] = $template;
		endif;

		if (Configure::read('debug') > 2) :
			$emailConfig['transport'] = 'Debug';
			$emailConfig['log'] 			= true;
		endif;

		if (isset($message)):
			$viewVars['message'] = $message;
		endif;

		$email = new CakeEmail();
		$email->config($emailConfig);
		$email->viewVars($viewVars);
		$email->send();
	
	} // end _contact()

	protected function _checkIfEditingIsAllowed($id = null) {
		if (is_null($id) && isset($this->passedArgs[0])) $id = $this->passedArgs[0];
		if (!is_null($id)) {
			if (
							$this->CurrentUser->getId() == $this->passedArgs[0]	 #users own_entry
							|| $this->CurrentUser['user_type']  == 'admin'				 #user is admin
			) {

				$this->allowedToEditUserData = true;
			}
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