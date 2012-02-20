<?php

	App::uses('Controller', 'Controller');
	App::uses('AppController', 'Controller');

	if ( !defined('FULL_BASE_URL') ) {
		define('FULL_BASE_URL', 'http://cakephp.org/');
	}

	class AppControllerTest extends ControllerTestCase {

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

		public function testLocalReferer() {
			$this->testAction('/entries/index');

			$_SERVER['HTTP_REFERER'] = FULL_BASE_URL . $this->controller->webroot . '/entries/index';
			$result = $this->controller->localReferer();
			$expected = '/entries/index';
			$this->assertIdentical($result, $expected);

			$_SERVER['HTTP_REFERER'] = FULL_BASE_URL . $this->controller->webroot . '/entries/view';
			$result = $this->controller->localReferer('action');
			$expected = 'view';
			$this->assertIdentical($result, $expected);

			$_SERVER['HTTP_REFERER'] = FULL_BASE_URL . $this->controller->webroot . '/some/path';
			$result = $this->controller->localReferer('controller');
			$expected = 'some';
			$this->assertIdentical($result, $expected);

			$_SERVER['HTTP_REFERER'] = FULL_BASE_URL . $this->controller->webroot . '/some/';
			$result = $this->controller->localReferer('action');
			$expected = 'index';
			$this->assertIdentical($result, $expected);

			$_SERVER['HTTP_REFERER'] = FULL_BASE_URL . $this->controller->webroot . '';
			$result = $this->controller->localReferer('action');
			$expected = 'index';
			$this->assertIdentical($result, $expected);

			$_SERVER['HTTP_REFERER'] = FULL_BASE_URL . $this->controller->webroot . '';
			$result = $this->controller->localReferer('controller');
			$expected = 'entries';
			$this->assertIdentical($result, $expected);

			//* external referer
			$_SERVER['HTTP_REFERER'] = 'http://heise.de/foobar/baz.html';
			$result = $this->controller->localReferer('controller');
			$expected = 'entries';
			$this->assertIdentical($result, $expected);

			$_SERVER['HTTP_REFERER'] = 'http://heise.de/foobar/baz.html';
			$result = $this->controller->localReferer('action');
			$expected = 'index';
			$this->assertIdentical($result, $expected);
		}

		public function testCurrentUser() {
			//* check there's no current user
			$result = $this->testAction('/entries/index', array( 'return' => 'vars' ));

			$this->assertTrue(is_null($result['CurrentUser']->getId()));
			$this->assertFalse($result['CurrentUser']->isLoggedIn());

			//* loginUser
			$Entries = $this->generate('Entries');
			$this->_loginUser(3);
			$result = $this->testAction(
					'/entries/index'
					, array( 'return' => 'vars' )
			);
			$this->assertEqual($result['CurrentUser']->getId(), 3);
			$this->assertTrue($result['CurrentUser']->isLoggedIn());
		}

		protected function _loginUser($id) {
			if ( isset($this->controller->Session) ) :
				$this->controller->Session->destroy();
			endif;

			$records = array(
					array(
							'id' => 1,
							'username' => 'Alice',
							'user_type' => 'admin',
							'user_email' => 'alice@example.com',
							// `test`
							'password' => '098f6bcd4621d373cade4e832627b4f6',
							'slidetab_order' => null,
							'user_automaticaly_mark_as_read' => 0,
							'user_lock' => 0,
					),
					array(
							'id' => 2,
							'username' => 'Mitch',
							'user_type' => 'mod',
							'user_email' => 'mitch@example.com',
							'password' => '098f6bcd4621d373cade4e832627b4f6',
							'slidetab_order' => null,
							'user_automaticaly_mark_as_read' => 0,
							'user_lock' => 0,
					),
					array(
							'id' => 3,
							'username' => 'Ulysses',
							'user_type' => 'user',
							'user_email' => 'ulysses@example.com',
							'password' => '098f6bcd4621d373cade4e832627b4f6',
							'slidetab_order' => null,
							'user_automaticaly_mark_as_read' => 0,
							'user_lock' => 0,
					),
					array(
							'id' => 5,
							'username' => 'Uma',
							'user_type' => 'user',
							'user_email' => 'uma@example.com',
							'password' => '098f6bcd4621d373cade4e832627b4f6',
							'slidetab_order' => null,
							'user_automaticaly_mark_as_read' => 1,
							'user_lock' => 0,
					),
			);

			$this->controller->Session->write('Auth.User', $records[$id-1]);
		}

		public function tearDown() {
			parent::tearDown();

			if ( isset($this->controller->Session) ) :
				$this->controller->Session->destroy();
			endif;
		}

	}

?>