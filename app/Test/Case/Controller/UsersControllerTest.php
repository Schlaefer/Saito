<?php

	App::uses('Controller', 'Controller');
	App::uses('UsersController', 'Controller');
	App::uses('SaitoControllerTestCase', 'Lib');

	class UsersControllerTestCase extends SaitoControllerTestCase {

		use SaitoSecurityMockTrait;

		public $fixtures = array(
			'app.bookmark',
			'app.user',
			'app.user_online',
			'app.user_read',
			'app.ecach',
			'app.entry',
			'app.category',
			'app.smiley',
			'app.smiley_code',
			'app.shout',
			'app.setting',
			'app.upload',
			'app.esnotification',
			'app.esevent',
		);

		const MAPQUEST = 'mapquestapi.com/sdk';

		public function testAdminAdd() {
			$data = [
				'User' => [
					'username' => 'foo',
					'user_email' => 'fo3@example.com',
					'user_password' => 'test',
					'password_confirm' => 'test',
				]
			];
			$expected = [
				'User' => [
					'username' => 'foo',
					'user_email' => 'fo3@example.com',
					'password' => 'test',
					'password_confirm' => 'test'
				]
			];

			$Users = $this->generate('Users', ['models' => ['User' => ['register']]]);
			$this->_loginUser(1);

			$Users->User->expects($this->once())
				->method('register')
				->with($expected, true);

			$this->testAction('/admin/users/add',
				['data' => $data, 'method' => 'post']);
		}

		public function testAdminAddNoAccess() {
			$data = [
				'User' => [
					'username' => 'foo',
					'user_email' => 'fo3@example.com',
					'user_password' => 'test',
					'password_confirm' => 'test',
				]
			];
			$Users = $this->generate('Users', ['models' => ['User' => ['register']]]);

			$Users->User->expects($this->never())
				->method('register');
			$this->setExpectedException('ForbiddenException');

			$this->testAction('/admin/users/add',
				['data' => $data, 'method' => 'post']);
		}

		public function testLogin() {
			$data = [
				'User' => [
					'username' => 'Ulysses',
					'password' => 'test'
				]
			];
			$this->testAction('/users/login', ['data' => $data]);

			$this->assertTrue($this->controller->CurrentUser->isLoggedIn());

			//# successful login redirects
			$this->assertRedirectedTo();

			//# last login time should be set
			$this->controller->User->id = 3;
			$user = $this->controller->User->read();
			$this->assertWithinMargin(time($user['User']['last_login']), time(), 1);
		}

		public function testLoginShowForm() {
			//# show login form
			$results = $this->testAction('/users/login',
				['method' => 'GET', 'return' => 'view']);
			$this->assertFalse(isset($this->headers['Location']));

			//## test username field
			$username = [
				'tag' => 'input',
				'attributes' => [
					'autocomplete' => 'off',
					'name' => 'data[User][username]',
					'required' => 'required',
					'tabindex' => '100',
					'type' => 'text'
				]
			];
			$this->assertTag($username, $results);

			//## test password field
			$password = [
				'tag' => 'input',
				'attributes' => [
					'autocomplete' => 'off',
					'name' => 'data[User][password]',
					'required' => 'required',
					'tabindex' => '101',
					'type' => 'password'
				]
			];
			$this->assertTag($password, $results);

			//# test logout on form show
			$this->assertFalse($this->controller->CurrentUser->isLoggedIn());
			$this->_loginUser(3);
			$user = $this->controller->Session->read('Auth.User');
			$this->controller->CurrentUser->setSettings($user);
			$this->assertTrue($this->controller->CurrentUser->isLoggedIn());
			$this->testAction('/users/login', ['method' => 'GET']);
			$this->assertFalse($this->controller->CurrentUser->isLoggedIn());
		}

		public function testLoginUserNotActivated() {
			$data = ['User' => ['username' => 'Diane', 'password' => 'test']];
			$result = $this->testAction('/users/login',
				['data' => $data, 'return' => 'contents']);
			$this->assertContains('is not activated yet.', $result);
		}

		public function testLoginUserLocked() {
			$data = ['User' => ['username' => 'Walt', 'password' => 'test']];
			$result = $this->testAction('/users/login',
				['data' => $data, 'return' => 'contents']);
			$this->assertContains('is locked.', $result);
		}

		public function testLogout() {
			$this->generate('Users');
			$this->_loginUser(3);
			$result = $this->testAction('/users/logout',
				['method' => 'GET', 'return' => 'contents']);
			$this->assertTag([
				'tag' => 'meta',
				'attributes' => [
					'http-equiv' => 'refresh',
					'content' => 'regexp:/1;\s/'
				]
			], $result);
		}

		/**
		 * Registration fails if Terms of Serice checkbox is not set in register form
		 */
		public function testRegisterTosNotSet() {
			$data = array(
				'User' => array(
					'username' => 'NewUser1',
					'user_email' => 'NewUser1@example.com',
					'user_password' => 'NewUser1spassword',
					'password_confirm' => 'NewUser1spassword',
					'tos_confirm' => '0'
				)
			);

			$Users = $this->generate('Users',
				array(
					'models' => array('User' => array('register'))
				));
			$Users->User->expects($this->never())
					->method('register');

			$result = $this->testAction('users/register',
				array('data' => $data, 'method' => 'post')
			);
		}

		public function testRegisterEmailFailed() {
			Configure::write('Saito.Settings.tos_enabled', false);
			$data = array(
				'User' => array(
					'username' => 'NewUser1',
					'user_email' => 'NewUser1@example.com',
					'user_password' => 'NewUser1spassword',
					'password_confirm' => 'NewUser1spassword',
				)
			);

			$Users = $this->generate('Users',
				[
					'components' => ['SaitoEmail' => ['email']],
					'methods' => ['email'],
					'models' => ['User' => ['register']]
				]);
			$Users->User->expects($this->once())
				->method('register')
				->will($this->returnValue(true));
			$Users->SaitoEmail->expects($this->once())
				->method('email')
				->will($this->throwException(new Exception));

			$result = $this->testAction('users/register',
				['data' => $data, 'method' => 'post', 'return' => 'view']
			);

			$this->assertContains('Sending Confirmation Email Failed', $result);
		}

		/**
		 * No TOS flag is send, but it's also not necessary
		 */
		public function testRegisterTosNotNecessary() {
			Configure::write('Saito.Settings.tos_enabled', false);

			$data = array(
				'User' => array(
					'username' => 'NewUser1',
					'user_email' => 'NewUser1@example.com',
					'user_password' => 'NewUser1spassword',
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
				array('data' => $data, 'method' => 'post')
			);
		}

		public function testRegisterViewForm() {
			$results = $this->testAction('/users/register',
				['method' => 'GET', 'return' => 'view']);
			$this->assertFalse(isset($this->headers['Location']));

			$fields = [
				'username' => [
					'tag' => 'input',
					'attributes' => [
						'autocomplete' => 'off',
						'name' => 'data[User][username]',
						'required' => 'required',
						'tabindex' => '1',
						'type' => 'text'
					]
				],
				'email' => [
					'tag' => 'input',
					'attributes' => [
						'autocomplete' => 'off',
						'name' => 'data[User][user_email]',
						'required' => 'required',
						'tabindex' => '2',
						'type' => 'text'
					]
				],
				'password' => [
					'tag' => 'input',
					'attributes' => [
						'autocomplete' => 'off',
						'name' => 'data[User][user_password]',
						'tabindex' => '3',
						'type' => 'password'
					]
				],
				'password_confirm' => [
					'tag' => 'input',
					'attributes' => [
						'autocomplete' => 'off',
						'name' => 'data[User][password_confirm]',
						'tabindex' => '4',
						'type' => 'password'
					]
				]
			];
			foreach ($fields as $field) {
				$this->assertTag($field, $results);
			}
		}

		public function testRegisterCheckboxNotOnPage() {
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
		 * Registration succeeds if Terms of Service checkbox is set in register form
		 */
		public function testRegisterTosSet() {
			$data = array(
				'User' => array(
					'username' => 'NewUser1',
					'user_email' => 'NewUser1@example.com',
					'user_password' => 'NewUser1spassword',
					'password_confirm' => 'NewUser1spassword',
					'tos_confirm' => '1'
				)
			);
			Configure::write('Saito.Settings.email_register', 'register@example.com');

			$Users = $this->generate('Users', ['models' => ['User' => ['register']]]);

			$user = $data;
			$user['User'] += [
				'id' => 48,
				'activate_code' => 151623
			];
			$Users->User->expects($this->once())
					->method('register')
					->will($this->returnValue($user));

			$result = $this->testAction('users/register',
				['data' => $data, 'method' => 'post', 'return' => 'vars']
			);

			//# test registration email
			$email = $result['email'];
			// test sender
			$this->assertContains('From: macnemo <register@example.com>',
				$email['headers']);
			// test registration link
			$this->assertContains('/users/rs/48?c=151623', $email['message']);
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
					'username' => "mITch",
					'user_email' => 'alice@example.com',
					'user_password' => 'NewUserspassword',
					'password_confirm' => 'NewUser1spassword',
				)
			);

			$Users = $this->generate('Users');
			$result = $this->testAction('users/register',
				array('data' => $data, 'method' => 'post', 'return' => 'view')
			);

			// Test that error strings are shown
			$this->assertContains('Email address is already used.', $result);
			$this->assertContains('Passwords don&#039;t match.', $result);
			$this->assertContains('Name is already used.', $result);
		}

		public function testRs() {
			$Users = $this->generate('Users', ['models' => ['User' => ['activate']]]);
			$Users->User->expects($this->once())
				->method('activate')
				->with(4, '1548')
				->will($this->returnValue(['status' => 'activated', 'User' => []]));
			$result = $this->testAction('/users/rs/4/?c=1548', ['return' => 'vars']);
			$this->assertEquals('activated', $result['status']);
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

		public function testViewProfileRequestByUsername() {
			$this->testAction('/users/view/Mitch');
			$this->assertContains('/users/name/Mitch', $this->headers['Location']);
		}

		public function testViewProfileForbiddenForAnon() {
			$this->testAction('/users/view/1');
			$this->assertContains('/login', $this->headers['Location']);
		}

		public function testViewProfileDoesNotExist() {
			$this->generate('Users');
			$this->_loginUser(3);
			$this->testAction('/users/view/9999');
			$this->assertRedirectedTo();
		}

		public function testView() {
			$userId = 3;
			$C = $this->generate('Users', ['models' => ['User' => ['countSolved']]]);
			$C->User->expects($this->once())
					->method('countSolved')
					->with($userId)
					->will($this->returnValue(16));
			$this->_loginUser(1);

			$result = $this->testAction("/users/view/$userId", ['return' => 'vars']);
			$this->assertFalse(isset($this->headers['Location']));
			$this->assertEquals($result['user']['User']['id'], 3);
			$this->assertEquals($result['user']['User']['username'], 'Ulysses');
			$this->assertEquals($result['user']['User']['solves_count'], '16');
		}

		public function testViewSanitation() {
			$this->generate('Users');
			$this->_loginUser(3);
			$result = $this->testAction('/users/view/7', ['return' => 'view']);

			$this->assertTextContains('&amp;&lt;Username', $result);
			$this->assertTextContains('&amp;&lt;RealName', $result);
			$this->assertTextContains('&amp;&lt;Homepage', $result);
			$this->assertTextContains('&amp;&lt;Place', $result);
			$this->assertTextContains('&amp;&lt;Profile', $result);
			$this->assertTextContains('&amp;&lt;Signature', $result);
			$this->assertTextNotContains('<&Username', $result);
		}

		public function testMapLinkInMenu() {
			$this->generate('Users');
			$this->_loginUser(3);

			// not enabled, no link
			$result = $this->testAction('/users/view/2', ['return' => 'contents']);
			$this->assertTextNotContains('/users/map', $result);
			$result = $this->testAction('/users/index', ['return' => 'contents']);
			$this->assertTextNotContains('/users/map', $result);

			// not enabled, link
			Configure::write('Saito.Settings.map_enabled', 1);
			$result = $this->testAction('/users/view/2', ['return' => 'contents']);
			$this->assertTextContains('/users/map', $result);
			$result = $this->testAction('/users/index', ['return' => 'contents']);
			$this->assertTextContains('/users/map', $result);
		}

		public function testMapDisabled() {
			$this->generate('Users');
			$this->_loginUser(3);
			$result = $this->testAction('/users/edit/3', ['return' => 'contents']);
			$this->assertTextNotContains('class="saito-usermap"', $result);
			$this->assertTextNotContains(static::MAPQUEST, $result);

			$result = $this->testAction('/users/view/3', ['return' => 'contents']);
			$this->assertTextNotContains('class="saito-usermap"', $result);
			$this->assertTextNotContains(static::MAPQUEST, $result);

			$result = $this->testAction('/users/map', ['return' => 'view']);
			$this->assertTextNotContains('class="saito-usermap"', $result);
			$this->assertRedirectedTo();
		}

		public function testMapActivated() {
			Configure::write('Saito.Settings.map_enabled', 1);

			$this->generate('Users');
			$this->_loginUser(3);
			$result = $this->testAction('/users/edit/3', ['return' => 'contents']);
			$this->assertTextContains('class="saito-usermap"', $result);
			$this->assertTextContains(static::MAPQUEST, $result);

			$result = $this->testAction('/users/view/2', ['return' => 'view']);
			$this->assertTextNotContains('class="saito-usermap"', $result);
			$result = $this->testAction('/users/view/3', ['return' => 'view']);
			$this->assertTextContains('class="saito-usermap"', $result);

			$result = $this->testAction('/users/map', ['return' => 'view']);
			$this->assertTextContains('class="saito-usermap"', $result);

			// Map CSS and JS should only be included on page if necessary
			$result = $this->testAction('/users/index', ['return' => 'contents']);
			$this->assertTextNotContains(static::MAPQUEST, $result);
		}

		public function testMapsNotLoggedIn() {
			$this->setExpectedException('MissingActionException');
			$this->testAction('/users/maps');
		}

		public function testName() {
			$this->generate('Users');
			$this->_loginUser(3);
			$this->testAction('/users/name/Mitch');
			$this->assertContains('/users/view/2', $this->headers['Location']);
		}

		public function testEditNotLoggedIn() {
			$this->setExpectedException('Saito\ForbiddenException');
			$this->testAction('/users/edit/3');
		}

		public function testEditNotUsersEntryGet() {
			$this->generate('Users');
			$this->_loginUser(2); // mod
			$this->setExpectedException('Saito\ForbiddenException');
			$this->testAction('/users/edit/3', ['method' => 'GET']);
		}

		public function testEditNotUsersEntryPost() {
			$this->generate('Users');
			$this->_loginUser(2); // mod
			$this->setExpectedException('Saito\ForbiddenException');
			$this->testAction('/users/edit/3', ['method' => 'POST']);
		}

		public function testEditNotUsersEntryButAdmin() {
			$this->generate('Users');
			$this->_loginUser(1); // mod
			$this->testAction('/users/edit/3', ['method' => 'POST']);
		}

		public function testIndex() {
			$this->generate('Users');
			$this->_loginUser(1);
			// basic test: creating view should not throw error
			$this->testAction('/users/index', ['return' => 'view']);
		}

		public function testLock() {
			/* not logged in should'nt be allowed */
			$this->testAction('/users/lock/3');
			$this->assertRedirectedTo();

			// user can't lock other users
			$this->_loginUser(3);
			$this->testAction('/users/lock/4');
			$this->controller->User->contain();
			$result = $this->controller->User->read('user_lock', 4);
			$this->assertTrue($result['User']['user_lock'] == false);

			// mod locks user
			$this->_loginUser(2);
			$this->testAction('/users/lock/4');
			$this->controller->User->contain();
			$result = $this->controller->User->read('user_lock', 4);
			$this->assertTrue($result['User']['user_lock'] == true);

			// mod unlocks user
			$this->testAction('/users/lock/4');
			$this->controller->User->contain();
			$result = $this->controller->User->read('user_lock', 4);
			$this->assertTrue($result['User']['user_lock'] == false);

			// you can't lock yourself out
			$this->testAction('/users/lock/2');
			$this->controller->User->contain();
			$result = $this->controller->User->read('user_lock', 2);
			$this->assertTrue($result['User']['user_lock'] == false);

			// mod can't lock admin
			$this->testAction('/users/lock/1');
			$this->controller->User->contain();
			$result = $this->controller->User->read('user_lock', 1);
			$this->assertTrue($result['User']['user_lock'] == false);

			// user does not exit
			$this->testAction('/users/lock/9999');
			$this->assertRedirectedTo();

			// locked user are thrown out
			$this->testAction('/users/lock/5');
			$this->controller->User->contain();
			$result = $this->controller->User->read('user_lock', 5);
			$this->assertTrue($result['User']['user_lock'] == true);
			$this->_logoutUser();

			$this->_loginUser(5);
			$this->testAction('/entries/index');
			$this->assertContains('users/logout', $this->headers['Location']);

			// locked user can't relogin
			$this->_logoutUser();
			$data = array(
				'User' => array(
					'username' => 'Uma',
					'password' => 'test',
				)
			);
			$this->testAction(
				'/users/login',
				array('data' => $data, 'method' => 'post')
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
				$this->assertTrue($result != false);
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
				$this->assertTrue($result != false);
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
				$this->assertTrue($result != false);
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
			$data = array('User' => array('modeDelete' => 1));
			$this->_loginUser(6);
			$this->testAction('/admin/users/delete/9999', array('data' => $data));
			$countAfterDelete = $this->controller->User->find('count');
			$this->assertEquals($countBeforeDelete, $countAfterDelete);
			$this->assertRedirectedTo();

			/*
			 * you can't delete yourself
			 */
			$data = array('User' => array('modeDelete' => 1));
			$this->_loginUser(6);
			$this->testAction('/admin/users/delete/6', array('data' => $data));
			$this->controller->User->contain();
			$result = $this->controller->User->findById(6);
			$this->assertTrue($result != false);

			/*
			 * you can't delete the root user
			 */
			$this->_loginUser(6);
			$this->testAction('/admin/users/delete/1', array('data' => $data));
			$this->controller->User->contain();
			$result = $this->controller->User->findById(1);
			$this->assertTrue($result != false);

			/*
			 * admin deletes user
			 */
			$this->_loginUser(6);
			$this->testAction('/admin/users/delete/5', array('data' => $data));
			$this->controller->User->contain();
			$result = $this->controller->User->findById(5);
			$this->assertEmpty($result);
			$this->assertRedirectedTo();
		}

		public function testChangePasswordNotLoggedIn() {
			$this->setExpectedException('Saito\ForbiddenException');
			$this->testAction('/users/changepassword/5');
			$this->assertRedirectedTo();
		}

		public function testChangePasswordWrongUser() {
			$this->generate('Users');
			$this->_loginUser(4);

			$this->setExpectedException('Saito\ForbiddenException');

			$data = [
				'User' => [
					'password_old' => 'test',
					'user_password' => 'test_new',
					'password_confirm' => 'test_new',
				]
			];
			$this->testAction('/users/changepassword/1',
				['data' => $data, 'method' => 'post']);
		}

		public function testChangePasswordViewFormWrongUser() {
			$this->generate('Users');
			$this->setExpectedException('Saito\ForbiddenException');
			$this->_loginUser(4);
			$this->testAction('/users/changepassword/5');
		}

		public function testChangePasswordViewForm() {
			$this->generate('Users');
			$this->_loginUser(4);
			$this->testAction('/users/changepassword/4');
			$this->assertFalse(isset($this->headers['location']));
		}

		public function testChangePasswordConfirmationFailed() {
			$this->generate('Users');
			$this->_loginUser(4);

			$data = [
				'User' => [
					'password_old' => 'test',
					'user_password' => 'test_new_foo',
					'password_confirm' => 'test_new_bar'
				]
			];
			$this->testAction('/users/changepassword/4',
				['data' => $data, 'method' => 'post']);
			$this->assertFalse($this->controller->User->validates());

			$expected = '098f6bcd4621d373cade4e832627b4f6';
			$this->controller->User->id = 4;
			$this->controller->User->contain();
			$result = $this->controller->User->read();
			$this->assertEquals($result['User']['password'], $expected);
			$this->assertFalse(isset($this->headers['Location']));
		}

		public function testChangePasswordOldPasswordNotCorrect() {
			$this->generate('Users');
			$this->_loginUser(4);

			$data = [
				'User' => [
					'password_old' => 'test_something',
					'user_password' => 'test_new_foo',
					'password_confirm' => 'test_new_foo',
				]
			];
			$this->testAction('/users/changepassword/4',
				['data' => $data, 'method' => 'post']);
			$this->assertFalse($this->controller->User->validates());

			$expected = '098f6bcd4621d373cade4e832627b4f6';
			$this->controller->User->id = 4;
			$this->controller->User->contain();
			$result = $this->controller->User->read();
			$this->assertEquals($result['User']['password'], $expected);
			$this->assertFalse(isset($this->headers['Location']));
		}

		public function testChangePassword() {
			$this->generate('Users');

			$this->_loginUser(5);
			$data = [
				'User' => [
					'password_old' => 'test',
					'user_password' => 'test_new',
					'password_confirm' => 'test_new',
				]
			];
			$this->testAction('/users/changepassword/5',
				['data' => $data, 'method' => 'post']);

			$this->controller->User->contain();
			$result = $this->controller->User->findById(5);

			App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');
			$pwH = new BlowfishPasswordHasher();

			$this->assertTrue($pwH->check('test_new', $result['User']['password']));
			$this->assertContains('users/edit', $this->headers['Location']);
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
			$this->assertTextNotContains('dropdown', $result);

			/**
			 * Mod Button is not visible for normal users
			 */
			$this->_loginUser(3);
			$result = $this->testAction('users/view/5', array(
					'return' => 'view'
			));
			$this->assertTextNotContains('dropdown', $result);

			/**
			 * Mod Button is visible for admin
			 */
			$this->_loginUser(1);
			$result = $this->testAction('users/view/5', array(
					'return' => 'view'
			));
			$this->assertTextContains('dropdown', $result);

			/**
			 * Mod Button is currently visible for mod
			 */
			$this->_loginUser(1);
			$result = $this->testAction('users/view/5', array(
					'return' => 'view'
			));
			$this->assertTextContains('dropdown', $result);
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
			$this->assertTextNotContains('dropdown', $result);
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
