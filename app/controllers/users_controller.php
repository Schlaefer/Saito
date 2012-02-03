<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of users_controller
 *
 * @author siezi
 */
class UsersController extends AppController {

	var $name = 'Users';
	var $components = array(
			'Email',
			);

	var $helpers = array (
			'Farbtastic',
			'Flattr.Flattr',
			'SimpleCaptcha.SimpleCaptcha',
			'TimeH',
			'EntryH',
	);
	
	protected $allowedToEditUserData = false;

	public function login() {
		/** set sub_nav_left * */
		$this->viewVars['LocationSubnavLeft'] = __('login_linkname', true);

		if ($this->Auth->user()) :
			//* login was successfull

			$this->User->id = $this->Session->read('Auth.User.id');
			$this->_successfulLogin();

			//* setting cookie
			if ( isset($this->data['User']['remember_me']) && $this->data['User']['remember_me']) :
				$this->CurrentUser->PersistentCookie->set();
				unset($this->data['User']['remember_me']);
			endif;

			//* handling redirect after successfull login
			if ($this->localReferer('action') == 'login') :
				$this->redirect($this->Auth->redirect());
			else :
				$this->redirect($this->referer());
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

		if (!empty($this->data)) {
			$this->data = $this->_passwordAuthSwitch($this->data);

			$this->data['User']['activate_code'] = mt_rand(1,9999999);
			$this->User->Behaviors->attach('SimpleCaptcha.SimpleCaptcha');
			if ($this->User->register($this->data)) {
					$this->data['User']['id'] = $this->User->id;

					$this->Email->to = $this->data['User']['user_email'];
					$this->Email->subject = 'Willkommen bei macnemo.de'; //@lo 
					$this->Email->from = Configure::read('Saito.Settings.forum_name') . ' <' . Configure::read('Saito.Settings.forum_email') . ">";
					$this->set('user', $this->data);
				  $this->Email->template = 'user_register';
					$this->Email->sendAs = 'text';
					$this->Email->send();

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
		if ( !empty($this->data) ) :
			$this->data = $this->_passwordAuthSwitch($this->data);
			$this->data['User']['activate_code'] = '';

			$this->User->create();
			if ( $this->User->register($this->data) ):
				$this->Session->setFlash('Nutzer erfolgreich angelegt @lo', 'flash/notice');
				$this->redirect(array( 'action' => 'view', $this->User->id ));
			endif;
		endif;
	}

	public function view($id = NULL) {
		$this->User->id = $id;

		$this->User->contain(array('UserOnline'));
		$viewed_user = $this->User->read();
		
		if (empty($this->data)) {
			if ($id == NULL || (!($viewed_user))) {
				$this->Session->setFlash((__('Invalid user', true)));
				$this->redirect('/');
			}
		}

		$viewed_user['User']["number_of_entries"] = $this->User->numberOfEntries();

		$this->set('lastEntries', $this->User->Entry->getRecentEntries(array( 'user_id' => $this->User->id) ));

		/** View Entry * */
		$this->set('user', $viewed_user);

		/** set sub_nav_left * */
	  $this->set('headerSubnavLeft', array('title' => __('back_to_forum_linkname',true), 'url' => '/'));

	}

	public function edit($id = NULL) {
		if (!$this->allowedToEditUserData || !$id && empty($this->data)) 
		{ /** no data to find entry or not allowed * */
			$this->Session->setFlash(__('Invalid user', true));
			$this->redirect('/');
		}

		// try to save entry
		if (!empty($this->data)) {

			$this->User->id = $id;

			if ($this->CurrentUser['user_type'] != 'admin') { /** make shure only admin can edit these fields * */
				# @td refactor this admin fields together with view: don't repeat code
				unset($this->data['User']['username']);
				unset($this->data['User']['user_email']);
				unset($this->data['User']['user_type']);
			}

			if ( $this->CurrentUser['user_type'] == 'mod' || $this->CurrentUser['user_type'] == 'admin' ) {
				unset($this->data['User']['new_posting_notify']);
				unset($this->data['User']['new_user_notify']);
			}

			if ($this->User->save($this->data)) {
				// save operation was successfull

				// if someone updates *his own* profile update settings for the session
				if ( $this->User->id == $this->CurrentUser->getId() ):
					// because we replace Auth.User we read the whole record again
					// for maybe empty fields such as username, user_email
					// @td recheck, probably not necessary after last [ref] of CurrentUser
					$this->User->contain();
					$this->data = $this->User->read();
					$this->CurrentUser->refresh();
				endif;

				$this->Session->setFlash(__('user_edit_success', true), 'flash/notice');
				$this->redirect(array('action' => 'view', $id));

			} else {
				// save operation failed

				# we possibly don't have username, user_type etc. in this->data on validation error
				# so we read old entry and merge with new data send by user
				$this->User->contain();
				$user = $this->User->read();
				$this->data['User'] = array_merge($user['User'], $this->data['User']);
				$this->User->set($this->data);
				$this->User->validates();
				$this->Session->setFlash(__('The user could not be saved. Please, try again.', true));
			}
		}


		if (empty($this->data)) { 
			//* View Entry by id 

			$this->User->id = $id;
			$this->User->contain('UserOnline');
			$this->User->sanitize(false);
			$this->data = $this->User->read();
		}
		$this->set('user', $this->data);

		/** set sub_nav_left **/
	  $this->set('headerSubnavLeft', array('title' => __('Back',true), 'url' => array ( 'controller' => 'users', 'action' => 'view', $this->User->id)));
	}

	public function changepassword($id = null) {
		if ( $id == null || !$this->_checkIfEditingIsAllowed($id) ) {
			$this->redirect('/');
		}

		$this->User->id = $id;
		$user = null;

		if (empty($this->data)) {
			# we have to fill it for the form magic to work
			$this->User->contain("UserOnline");
			$user = $this->User->read();
			$user['User']['password'] = '';
			$this->data = $user;
		}
		else
		{
			$this->data = $this->_passwordAuthSwitch($this->data);
			$this->User->id = $id;
			$this->User->contain('UserOnline');
			if ($this->User->save($this->data)) {
				$this->Session->setFlash(__('change_password_success', true));
				$this->redirect( array('controller'=>'users', 'action'=>'edit', $id));
			}
			$this->data['User']['id'] = $id;
		}

	  $this->set('headerSubnavLeft', array('title' => __('Back',true), 'url' => array ( 'controller' => 'users', 'action' => 'edit', $id)));
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
		if (!$user || !$user['User']['personal_messages']) :
			$this->redirect('/');
		endif;

		$send = false;

		if ($this->data) {
			$subject = rtrim($this->data['Message']['subject']);
			if (empty($subject)) {
				$this->Session->setFlash('Betreff darf nicht leer sein.'); # @lo
				$this->data = $user;
			} else {
				try {
					$this->_contact($user, $this->CurrentUser->getId(), $subject, $this->data['Message']['text']);
					$send = true;
					$this->Session->setFlash('Nachricht wurde versandt.', 'flash/notice'); # @lo
					$this->redirect('/');
				} catch (Exception $exc) {
					$this->Session->setFlash('Nachricht konnte nicht versandt werden.', 'flash/error'); # @lo
				} // end try
			} // end if
		} // end if($this->data)

		$this->data = $user;
		$this->set('send', $send);
	} // end contact()

	public function ajax_toggle($toggle) {
		if(!$this->CurrentUser->isLoggedIn() || !$this->RequestHandler->isAjax()) $this->redirect('/');

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
		if(!$this->CurrentUser->isLoggedIn() || !$this->RequestHandler->isAjax()) $this->redirect('/');

		$this->autoRender = false;

		if (isset($this->data['User']['slidetab_order'])) {
			$out = $this->data['User']['slidetab_order'];
			$out = array_filter($out, 'strlen');
			$out = serialize($out);

			$this->User->id = $this->CurrentUser->getId();
			$this->User->saveField('slidetab_order', $out);
			$this->CurrentUser['slidetab_order'] = $out;
		}

		return $this->data;
	}

	public function beforeFilter() {
		Stopwatch::start('Users->beforeFilter()');
		parent::beforeFilter();

		$this->Auth->allow('register', 'login', 'contact');

		if ($this->action === 'view') {
			$this->_checkIfEditingIsAllowed();
			$this->_loadSmilies();
		}
		if ($this->action === 'edit') {
			$this->_checkIfEditingIsAllowed();
		}

		if (Configure::read('debug') > 0) {
			$this->Email->delivery = 'debug';
			}

		Stopwatch::stop('Users->beforeFilter()');
	}

	protected function _contact($recipient, $sender, $subject, $message) {
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

		$this->Email->to = $recipient['User']['user_email'];
		$this->Email->subject = $subject;
		$this->Email->from = $sender['User']['username'] . ' <' . $sender['User']['user_email'] . '>';
		$this->set('message', $message);
		$this->Email->template = 'user_contact';
		$this->Email->sendAs = 'text';
		$this->Email->send();
	
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