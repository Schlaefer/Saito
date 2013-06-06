<?php

	App::uses('Controller', 'Controller');
	App::uses('UsersController', 'Controller');
	App::uses('SaitoControllerTestCase', 'Lib');

	class UsersControllerTestCase extends SaitoControllerTestCase {

		public $fixtures = array(
				'app.bookmark',
				'app.user',
				'app.user_online',
				'app.ecach',
				'app.entry',
				'app.category',
				'app.smiley',
				'app.smiley_code',
				'app.setting',
				'app.upload',
				'app.esnotification',
				'app.esevent',
		);

		public function testAdminAdd() {
			$data = array(
					'User' => array(
							'username'				 => 'foo',
							'user_email'			 => 'fo3@example.com',
							'user_password'		 => 'test',
							'password_confirm' => 'test',
					));
			$expected					 = array(
					'User' => array(
							'username'				 => 'foo',
							'user_email'			 => 'fo3@example.com',
							'password'				 => 'test',
							'password_confirm' => 'test',
					));
			$Users						 = $this->generate('Users',
					array(
					'models' => array(
							'User' => array('register')
					)
					));
			$this->_loginUser(1);
			$Users->User->expects($this->once())
					->method('register')
					->with($expected);
			$this->testAction('/admin/users/add',
					array(
					'data'	 => $data, 'method' => 'post'
			));
		}

		public function testAdminAddNoAccess() {
			$data = array(
					'User' => array(
							'username'				 => 'foo',
							'user_email'			 => 'fo3@example.com',
							'user_password'		 => 'test',
							'password_confirm' => 'test',
					));
			$Users						 = $this->generate('Users',
					array(
					'models' => array(
							'User'
					)
					));
			$Users->User->expects($this->never())
					->method('register');
			$this->expectException('ForbiddenException');
			$this->testAction('/admin/users/add',
					array(
					'data'	 => $data, 'method' => 'post'
			));
		}

		public function testLogin() {

			//* user sees login form
			$this->_logoutUser();
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

			// check that there was no false insertion of new users through relationships
			// leave this test of the end of testLogin()
			$registeredUsersAfterLogin = $this->Users->User->find('count');
			$this->assertEqual($registeredUsersBeforeLogin, $registeredUsersAfterLogin);

		}

		/**
		 * Registration fails if Terms of Serice checkbox is not set in register form
		 */
		public function testRegisterTosNotSet() {

			$data = array(
					'User' => array(
							'username'				 => 'NewUser1',
							'user_email'			 => 'NewUser1@example.com',
							'user_password'		 => 'NewUser1spassword',
							'password_confirm' => 'NewUser1spassword',
							'tos_confirm'			 => '0'
					)
			);

			$Users = $this->generate('Users',
					array(
					'models' => array('User' => array('register'))
					));
			$Users->User->expects($this->never())
					->method('register');

			$result = $this->testAction('users/register',
					array('data'	 => $data, 'method' => 'post')
			);
		}

		/**
		 * No TOS flag is send, but it's also not necessary
		 */
		public function testRegisterTosNotNecessary() {

			Configure::write('Saito.Settings.tos_enabled', false);

			$data = array(
					'User' => array(
							'username'				 => 'NewUser1',
							'user_email'			 => 'NewUser1@example.com',
							'user_password'		 => 'NewUser1spassword',
							'password_confirm' => 'NewUser1spassword',
					)
			);

			$Users = $this->generate('Users',
					array(
					'models' => array('User' => array('register'))
					));
			$Users->User->expects($this->once())
					->method('register');

			$result = $this->testAction('users/register',
					array('data'	 => $data, 'method' => 'post')
			);
		}

		public function tes1RegisterCheckboxNotOnPage() {
			Configure::write('Saito.Settings.tos_enabled', false);
			$result = $this->testAction('users/register', array('return' => 'view'));
			$this->assertNotContains('data[User][tos_confirm]', $result);
			$this->assertNotContains('http://example.com/tos-url.html/', $result);
			$this->assertNotContains('disabled', $result);
		}

		public function testRegisterCheckboxOnPage() {
			$result = $this->testAction('users/register', array('return' => 'view'));
			$this->assertContains('data[User][tos_confirm]', $result);
			$this->assertContains('http://example.com/tos-url.html/', $result);
			$this->assertContains('disabled="disabled"', $result);
		}

		public function testRegisterCheckboxOnPageCustomTosUrl() {
			Configure::write('Saito.Settings.tos_url', '');
			$result = $this->testAction('users/register', array('return' => 'view'));
			$this->assertContains($this->controller->request->webroot . 'pages/eng/tos', $result);
		}

		/**
		 * Registration succeds if Terms of Serice checkbox is set in register form
		 */
		public function testRegisterTosSet() {

			$data = array(
					'User' => array(
							'username'				 => 'NewUser1',
							'user_email'			 => 'NewUser1@example.com',
							'user_password'		 => 'NewUser1spassword',
							'password_confirm' => 'NewUser1spassword',
							'tos_confirm'			 => '1'
					)
			);

			$Users = $this->generate('Users',
					array(
					'methods' => array('email'),
					'models' => array('User' => array('register'))
					));
			$Users->User->expects($this->once())
					->method('register')
					->will($this->returnValue(true));

			$result = $this->testAction('users/register',
					array('data'	 => $data, 'method' => 'post')
			);
		}

		/**
		 * There's already an test for validation errors in UserTest, but registration
		 * is seldom used and so we make sure with this test that validation error
		 * message are really shown.
		 */
		public function testRegisterValidation() {
			Configure::write('Saito.Settings.tos_enabled', false);

			$data = array(
					'User' => array(
							'username'				 => 'mitch',
							'user_email'			 => 'alice@example.com',
							'user_password'		 => 'NewUserspassword',
							'password_confirm' => 'NewUser1spassword',
					)
			);

			$Users = $this->generate('Users');
			$result = $this->testAction('users/register',
					array('data'	 => $data, 'method' => 'post', 'return' => 'view')
			);

			// Test that error strings are shown
			$this->assertContains('Email address is already used.', $result);
			$this->assertContains('Passwords don&#039;t match.', $result);
			$this->assertContains('Name is already used.', $result);
		}

		public function testSetcategoryNotLoggedIn() {
			$this->setExpectedException('ForbiddenException');
			$this->testAction('/users/setcategory/all');
		}

		public function testSetcategoryAll() {
			$Users = $this->generate(
				'Users',
				array('models' => array('User' => array('setCategory')))
			);

			$this->_loginUser(3);

			$Users->User->expects($this->once())
					->method('setCategory')
					->with('all');

			$this->testAction('/users/setcategory/all');
		}

		public function testSetcategoryCategory() {
			$Users = $this->generate(
				'Users',
				array('models' => array('User' => array('setCategory')))
			);
			$this->_loginUser(3);
			$Users->User->expects($this->once())
					->method('setCategory')
					->with(5);
			$this->testAction('/users/setcategory/5');
		}

		public function testSetcategoryCategories() {
			$Users = $this->generate(
				'Users',
				array('models' => array('User' => array('setCategory')))
			);

			$this->_loginUser(3);

			$data = array(
				'CatChooser' => array(
					'4' => '0',
					'7' => '1',
					'9' => '0',
				),
				'CatMeta' => array(
					'All' => '1',
				)
			);

			$Users->User->expects($this->once())
					->method('setCategory')
					->with($data['CatChooser']);
			$this->testAction(
				'/users/setcategory/',
				array('data' => $data, 'method' => 'post')
			);
		}

		public function testView() {
			/*
			 * unregistred users can't see user profiles
			 */
			$result = $this->testAction('/users/view/1');
			$this->assertContains('/login', $this->headers['Location']);

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
			 * Test profile request by username
			 */
			$this->testAction('/users/view/Mitch');
			$this->assertContains('/users/name/Mitch', $this->headers['Location']);

			/*
			 * if user (profile) doesn't exist
			 */
			$this->testAction('/users/view/9999');
			$this->assertEqual(
				FULL_BASE_URL . $this->controller->request->webroot,
				$this->headers['Location']
			);

		}

		public function testName() {
			$this->generate('Users');
			$this->_loginUser(3);
			$this->testAction('/users/name/Mitch');
			$this->assertContains('/users/view/2', $this->headers['Location']);
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

    public function testDelete() {
			/*
       *  not logged in can't delete
       */
      try {
        $this->testAction('/admin/users/delete/3');
      } catch (ForbiddenException $exc) {
        $this->controller->User->contain();
        $result = $this->controller->User->findById(3);
        $this->assertTrue($result != FALSE);
      }

      /*
       * user can't delete admin/users
       */
      $this->_loginUser(3);
      try {
        $this->testAction('/admin/users/delete/4');
      } catch (ForbiddenException $exc) {
        $this->controller->User->contain();
        $result = $this->controller->User->findById(4);
        $this->assertTrue($result != FALSE);
      }

      /*
       *  mods can't delete admin/users
       */
      $this->_loginUser(2);
      try {
        $this->testAction('/admin/users/delete/4');
      } catch (ForbiddenException $exc) {
        $this->controller->User->contain();
        $result = $this->controller->User->findById(4);
        $this->assertTrue($result != FALSE);
      }

      /*
       *  admin can access delete ui
       */
      $this->_loginUser(6);
      $this->testAction('/admin/users/delete/4');
			$this->assertFalse(isset($this->headers['location']));

      /*
       * you can't delete non existing users
       */
      $countBeforeDelete = $this->controller->User->find('count');
      $data = array ('User' => array ('modeDelete' => 1));
      $this->_loginUser(6);
			$this->testAction( '/admin/users/delete/9999', array( 'data' => $data));
      $countAfterDelete = $this->controller->User->find('count');
      $this->assertEqual($countBeforeDelete, $countAfterDelete);
			$this->assertEqual(FULL_BASE_URL . $this->controller->request->webroot, $this->headers['Location']);

      /*
       * you can't delete yourself
       */
      $data = array ('User' => array ('modeDelete' => 1));
      $this->_loginUser(6);
			$this->testAction( '/admin/users/delete/6', array( 'data' => $data));
      $this->controller->User->contain();
      $result = $this->controller->User->findById(6);
      $this->assertTrue($result != FALSE);

      /*
       * you can't delete the root user
       */
      $this->_loginUser(6);
			$this->testAction( '/admin/users/delete/1', array( 'data' => $data));
      $this->controller->User->contain();
      $result = $this->controller->User->findById(1);
      $this->assertTrue($result != FALSE);

      /*
       * admin deletes user
       */
      $this->_loginUser(6);
			$this->testAction( '/admin/users/delete/5', array( 'data' => $data));
      $this->controller->User->contain();
      $result = $this->controller->User->findById(5);
      $this->assertEmpty($result);
			$this->assertEqual(FULL_BASE_URL . $this->controller->request->webroot, $this->headers['Location']);
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

      $this->controller->User->contain();
      $result = $this->controller->User->findById(5);
      $this->assertTrue(BcryptAuthenticate::checkPassword('test_new', $result['User']['password']));
			$this->assertContains('users/edit', $this->headers['Location']);



    }

		public function testContactForbidden() {
			/* not logged in but contacting forum is always allowed */
			$this->testAction('/users/contact/0');
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

		public function testContactAnon() {

			$data = array(
											'Message' => array(
											'sender_contact' => 'fo3@example.com',
											'subject'				 => 'subject',
											'text'					 => 'text',
					));
			$Users = $this->generate('Users',
					array(
									'components' => array('SaitoEmail' => array('email'))
							));
			$Users->SaitoEmail->expects($this->once())
					->method('email');
			$this->testAction('/users/contact/0',
					array(
										 'data'	 => $data,
										 'method' => 'post',
			));
			$this->assertContains($this->controller->request->webroot, $this->headers['Location']);
		}

		public function testContactNoSubject() {

			$data = array(
											'Message' => array(
											'sender_contact' => 'fo3@example.com',
											'subject'				 => '',
											'text'	 => 'text',
					));
			$Users = $this->generate('Users',
					array(
									'components' => array('SaitoEmail' => array('email'))
							));
			$Users->SaitoEmail->expects($this->never())
					->method('email');
			$result = $this->testAction('/users/contact/0',
					array(
															 'data'	 => $data,
															 'method' => 'post',
															 'return' => 'contents',
					));
			$this->assertContains(
				'"type":"error","channel":"form","element":"#MessageSubject"',
				$result
			);
		}

		public function testContactNoValidEmail() {

			$data = array(
											'Message' => array(
											'sender_contact' => '',
											'subject'				 => 'Subject',
											'text'	 => 'text',
					));
			$Users = $this->generate('Users',
					array(
									'components' => array('SaitoEmail' => array('email'))
							));
			$Users->SaitoEmail->expects($this->never())
					->method('email');
			$result = $this->testAction('/users/contact/0',
					array(
															 'data'	 => $data,
															 'method' => 'post',
															 'return' => 'contents',
					));
			$this->assertContains(
				// @todo make independed from i18n string
				'"type":"error","channel":"form","element":"#MessageSenderContact"',
				$result
			);
		}

		/**
		 * Checks that the mod-button is in-/visible
		 */
		public function testViewModButton() {

			$Users = $this->generate('Users');

			/**
			 * Mod Button is not visible for anon users
			 */
			$result = $this->testAction('users/view/5', array(
					'return' => 'view'
			));
			$this->assertTextNotContains('button_mod_panel', $result);

			/**
			 * Mod Button is not visible for normal users
			 */
			$this->_loginUser(3);
			$result = $this->testAction('users/view/5', array(
					'return' => 'view'
			));
			$this->assertTextNotContains('button_mod_panel', $result);

			/**
			 * Mod Button is visible for admin
			 */
			$this->_loginUser(1);
			$result = $this->testAction('users/view/5', array(
					'return' => 'view'
			));
			$this->assertTextContains('button_mod_panel', $result);

			/**
			 * Mod Button is currently visible for mod
			 */
			$this->_loginUser(1);
			$result = $this->testAction('users/view/5', array(
					'return' => 'view'
			));
			$this->assertTextContains('button_mod_panel', $result);

		}

		public function testViewModButtonEmpty() {

			/**
			 * Mod menu is currently empty for mod
			 */
			Configure::write('Saito.Settings.block_user_ui', false);

			$Users = $this->generate('Users');

			$this->_loginUser(2);
			$result = $this->testAction('users/view/5', array(
					'return' => 'view'
			));
			$this->assertTextNotContains('button_mod_panel', $result);
		}

		public function testViewModButtonBlockUiTrue() {

			Configure::write('Saito.Settings.block_user_ui', true);

			$Users = $this->generate('Users');

			$this->_loginUser(2);
			$result = $this->testAction('users/view/5', array(
					'return' => 'view'
			));
			$this->assertTextContains('users/lock/5', $result);
		}

		public function testViewModButtonBlockUiFalse() {

			Configure::write('Saito.Settings.block_user_ui', false);

			$Users = $this->generate('Users');

			$this->_loginUser(2);
			$result = $this->testAction('users/view/5', array(
					'return' => 'view'
			));
			$this->assertTextNotContains('users/lock/5', $result);
		}

	}

?>