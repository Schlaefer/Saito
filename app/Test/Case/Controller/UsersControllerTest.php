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

    public function testLock() {

			/* not logged in should'nt be allowed */
			$this->testAction('/users/lock/3');
			$this->assertEqual(FULL_BASE_URL . $this->controller->request->webroot, $this->headers['Location']);

      // user can't lock other users
      $this->_loginUser(3);
			$this->testAction('/users/lock/4');
      $this->controller->User->contain();
      $result = $this->controller->User->read('user_lock', 4);
      $this->assertTrue($result['User']['user_lock'] == FALSE);

      // mod locks user
      $this->_loginUser(2);
			$this->testAction('/users/lock/4');
      $this->controller->User->contain();
      $result = $this->controller->User->read('user_lock', 4);
      $this->assertTrue($result['User']['user_lock'] == TRUE);

      // mod unlocks user
			$this->testAction('/users/lock/4');
      $this->controller->User->contain();
      $result = $this->controller->User->read('user_lock', 4);
      $this->assertTrue($result['User']['user_lock'] == FALSE);

      // you can't lock yourself out
			$this->testAction('/users/lock/2');
      $this->controller->User->contain();
      $result = $this->controller->User->read('user_lock', 2);
      $this->assertTrue($result['User']['user_lock'] == FALSE);

      // mod can't lock admin
			$this->testAction('/users/lock/1');
      $this->controller->User->contain();
      $result = $this->controller->User->read('user_lock', 1);
      $this->assertTrue($result['User']['user_lock'] == FALSE);

      // user does not exit
			$this->testAction('/users/lock/9999');
			$this->assertEqual(FULL_BASE_URL . $this->controller->request->webroot, $this->headers['Location']);


      // locked user are thrown out
			$this->testAction('/users/lock/5');
      $this->controller->User->contain();
      $result = $this->controller->User->read('user_lock', 5);
      $this->assertTrue($result['User']['user_lock'] == TRUE);
      $this->_logoutUser();

      $this->_loginUser(5);
			$this->testAction('/entries/index');
			$this->assertContains('users/logout', $this->headers['Location']);


      // locked user can't relogin
      $this->_logoutUser();
      $data = array(
          'User' => array(
              'username'          => 'Uma',
              'password'          => 'test',
          )
        );
      $this->testAction(
          '/users/login',
          array('data'  => $data, 'method'  => 'post')
          );
      $this->assertNull($this->controller->Session->read('Auth.User'));
    }

    public function testChangePassword() {

      // not logged in user can't change password
      $this->testAction('/users/changepassword/5');
			$this->assertEqual(FULL_BASE_URL . $this->controller->request->webroot, $this->headers['Location']);

      // user (4) shouldn't see change password dialog of other users (5)
      $this->_loginUser(4);
      $result = $this->testAction('/users/changepassword/5');
			$this->assertEqual(FULL_BASE_URL . $this->controller->request->webroot, $this->headers['Location']);

      // user has access to his own changepassword dialog
      $result = $this->testAction('/users/changepassword/4');
			$this->assertFalse(isset($this->headers['location']));

      /*
       * test changing password
       */
      $this->_loginUser(5);
      $data = array(
          'User' => array(
              'password_old'      => 'test',
              'user_password'     => 'test_new',
              'password_confirm'  => 'test_new',
          )
        );
      $this->testAction(
          '/users/changepassword/5',
          array('data'  => $data, 'method'  => 'post')
          );

      $expected = 'bc681d86e82c720af115786cb716a25e';
      $this->controller->User->id = 5;
      $this->controller->User->contain();
      $result = $this->controller->User->read();
      $this->assertEqual($result['User']['password'], $expected);
			$this->assertContains('users/edit', $this->headers['Location']);
      
      /*
       * test password confirmation failed
       */
      $this->_loginUser(4);
      $data = array(
          'User' => array(
              'password_old'      => 'test',
              'user_password'     => 'test_new_foo',
              'password_confirm'  => 'test_new_bar',
          )
        );
      $this->testAction(
          '/users/changepassword/4',
          array('data'  => $data, 'method'  => 'post')
          );
      $this->assertFalse($this->controller->User->validates());

      $expected = '098f6bcd4621d373cade4e832627b4f6';
      $this->controller->User->id = 4;
      $this->controller->User->contain();
      $result = $this->controller->User->read();
      $this->assertEqual($result['User']['password'], $expected);
			$this->assertFalse(isset($this->headers['Location']));

      /*
       * test old passwort not correct
       */
      $data = array(
          'User' => array(
              'password_old'      => 'test_something',
              'user_password'     => 'test_new_foo',
              'password_confirm'  => 'test_new_foo',
          )
        );
      $this->testAction(
          '/users/changepassword/4',
          array('data'  => $data, 'method'  => 'post')
          );
      $this->assertFalse($this->controller->User->validates());

      $expected = '098f6bcd4621d373cade4e832627b4f6';
      $this->controller->User->id = 4;
      $this->controller->User->contain();
      $result = $this->controller->User->read();
      $this->assertEqual($result['User']['password'], $expected);
			$this->assertFalse(isset($this->headers['Location']));


      /*
       * test change password of other users not allowed
       */
      $data = array(
          'User' => array(
              'password_old'      => 'test',
              'user_password'     => 'test_new',
              'password_confirm'  => 'test_new',
          )
        );
      $this->testAction(
          '/users/changepassword/1',
          array('data'  => $data, 'method'  => 'post')
          );

      $expected = '098f6bcd4621d373cade4e832627b4f6';
      $this->controller->User->id = 1;
      $this->controller->User->contain();
      $result = $this->controller->User->read();
      $this->assertEqual($result['User']['password'], $expected);
			$this->assertEqual(FULL_BASE_URL . $this->controller->request->webroot, $this->headers['Location']);


    }

		public function testContactForbidden() {
			/* not logged in but contacting admin is always allowed */
			$this->testAction('/users/contact/1');
			$this->assertFalse(isset($this->headers['Location']));

			/* not logged in should'nt be allowed */
			$this->testAction('/users/contact/3');
			$this->assertEqual(FULL_BASE_URL . $this->controller->request->webroot, $this->headers['Location']);

			/* not logged in should'nt be allowed */
			$this->testAction('/users/contact/5');
			$this->assertEqual(FULL_BASE_URL . $this->controller->request->webroot, $this->headers['Location']);

			/* logged in and allowed */
			$this->_loginUser(2);
			$this->testAction('/users/contact/3');
			$this->assertFalse(isset($this->headers['location']));
			$this->assertContains('/users/contact/3', $this->controller->request->here);

			/* logged in but recipient's user-pref doesn't allow it  */
			$this->testAction('/users/contact/5');
			$this->assertEqual(FULL_BASE_URL . $this->controller->request->webroot, $this->headers['Location']);

			/* no recipient id */
			$this->testAction('/users/contact/');
			$this->assertEqual(FULL_BASE_URL . $this->controller->request->webroot, $this->headers['Location']);

			/* recipient does not exist */
			$this->testAction('/users/contact/9999');
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