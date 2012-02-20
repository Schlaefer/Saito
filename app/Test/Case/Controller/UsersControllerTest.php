<?php

	App::uses('Controller', 'Controller');
	App::uses('UsersController', 'Controller');
	App::uses('SaitoControllerTestCase', 'Lib');

	class UsersControllerTestCase extends SaitoControllerTestCase {

		public $fixtures = array(
				'app.user',
				'app.user_online',
				'app.entry',
				'app.category',
				'app.smiley',
				'app.smiley_code',
				'app.setting',
				'app.upload'
		);

		public function testLogin() {

			//* user sees login form
			$result = $this->testAction('/users/login');
			$this->assertFalse(isset($this->headers['Location']));

			return;

			//* users logged in
			$this->Users->Session->write('Auth.User',
					array(
					'id' => 3,
					'username' => 'Ulysses',
			));

			//registred user before login try
			$registeredUsersBeforeLogin = $this->Users->User->find('count');

			$this->_prepareAction('/users/login');
			$timeOfLogin = date('Y-m-d H:i:s');
			$this->Users->login();
			$this->Users->User->id = 3;
			$userAfterLogin = $this->Users->User->read();

			// redirect
			$this->assertEqual($this->Users->redirectUrl, $this->Users->referer());
			// user has to be in useronline
			$this->assertTrue($this->Users->User->UserOnline->findByUserId(3));

			// time is stored as last login time
			$this->assertEqual($timeOfLogin, $userAfterLogin['User']['last_login']);

//		debug($this->Users->User->read());
			// check that there was no false insertion of new users through relationships
			// leave this test of the end of testLogin()
			$registeredUsersAfterLogin = $this->Users->User->find('count');
			$this->assertEqual($registeredUsersBeforeLogin, $registeredUsersAfterLogin);

//		debug($this->Users->data);
//		debug($this->Users->viewVars);
//		debug($this->Users->Auth->user());
//		debug($this->Users->renderedAction);
//		debug($this->Users->redirectUrl);
//		debug($this->Users->currentUser);
//		debug($result);
		}

		public function testView() {
			/*
			 * unregistred users can't see user profiles
			 */
			$result = $this->testAction('/users/view/1');
			$this->assertContains('/users/login', $this->headers['Location']);

			/*
			 * registred users can see user profiles
			 */
			$this->_loginUser(3);
			$result = $this->testAction('/users/view/1');
			$this->assertFalse(isset($this->headers['Location']));

			$result = $this->testAction('/users/view/1', array( 'return' => 'vars' ));
			$this->assertEqual($result['user']['User']['id'], 1);
			$this->assertEqual($result['user']['User']['username'], 'Alice');

			/*
			 * if user (profile) doesn't exist
			 */
			$result = $this->testAction('/users/view/9999');
			$this->assertEqual(FULL_BASE_URL . $this->controller->request->webroot, $this->headers['Location']);
		}

		public function testIndexGetViewVarsRegistred() {
			/*
			  $this->_loginUser("Charles");
			  foreach ($this->r as $name => $userdata) {
			  $this->prepareAction('/users/view', $userdata['user_id']);
			  $result = $this->testAction('/users/view/'.$userdata['user_id'], array( 'return' => 'vars' ));
			  $fields =  array_keys($userdata);
			  foreach($fields as $field) {
			  $this->assertEqual($result['user']['User'][$field], $userdata[$field]);
			  }
			  }
			 */
		}

		public function testGetViewVarsChangepassword() {

			/*
			  $this->_loginUser("Charles");
			  $this->prepareAction('/users/changepassword', $this->cu['user_id']);
			  $result = $this->testAction('/users/changepassword/'.$this->cu['user_id'], array( 'return' => 'vars' ));
			  $this->assertTrue($result['allowedToEditUserData']);
			 */
		}

	}

?>