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

			/* cake2
			//* loginUser
			$this->_loginUser(3);
			$this->_prepareAction('/entries/index');
			$result = $this->testAction('/entries/index', array( 'return' => 'vars' ));
			//* check there's no current user
			$this->assertEqual($result['CurrentUser']->getId(), 3);
			$this->assertTrue($result['CurrentUser']->isLoggedIn());
			 *
			 */
		}

	}

?>