<?php

	// load fixture
	App::uses('UserFixture', 'Fixture');
	App::uses('SaitoControllerTestCase', 'Lib');

	// sets the FULL_BASE_URL for CLI tests
	if (!defined('FULL_BASE_URL')) {
		define('FULL_BASE_URL', 'http://cakephp.org/');
	}
	Configure::write('App.fullBaseURL', FULL_BASE_URL);


	class SaitoControllerTestCase extends ControllerTestCase {

/**
 * Preserves $GLOBALS vars through PHPUnit test runs
 *
 * @see http://www.phpunit.de/manual/3.6/en/fixtures.html#fixtures.global-state
 * @var array
 */
		// @codingStandardsIgnoreStart
		protected $backupGlobalsBlacklist = array(
			/*
			 * $GLOBALS['__STRINGPARSER_NODE_ID' is set in stringparser.class.php
			 * and must not cleared out
			 */
			'__STRINGPARSER_NODE_ID'
		);
		// @codingStandardsIgnoreEnd

		protected function _setJson() {
			$_SERVER['HTTP_ACCEPT'] = 'application/json, text/javascript';
		}

		protected function _unsetJson() {
			$_SERVER['HTTP_ACCEPT'] = "text/html,application/xhtml+xml,application/xml";
		}

		protected function _setAjax() {
			$_ENV['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		}

		protected function _unsetAjax() {
			unset($_ENV['HTTP_X_REQUESTED_WITH']);
		}

		protected function _loginUser($id) {
			/*
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
							'id' => 4,
							'username' => 'Change Password Test',
							'user_type' => 'user',
							'user_email' => 'cpw@example.com',
							'password' => '098f6bcd4621d373cade4e832627b4f6',
							'slidetab_order' => null,
							'user_automaticaly_mark_as_read' => 1,
							'user_lock' => 0,
							'personal_messages' => 0,
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
			*/

			// see http://stackoverflow.com/a/10411128/1372085

			$this->_logoutUser();
			$userFixture = new UserFixture();
			$users = $userFixture->records;

			$this->controller->Session->write('Auth.User', $users[$id - 1]);
		}

		public function assertRedirectedTo($url = '') {
			$this->assertEqual(
				Router::fullBaseUrl() . $this->controller->request->webroot . $url,
				$this->headers['Location']
			);
		}

		public function generate($controller, $mocks = []) {
			$byPassSecurity = false;
			if (!isset($mocks['components']['Security'])) {
				$byPassSecurity = true;
				$mocks['components']['Security'] = ['_validateCsrf', '_validatePost'];
			}
			$Mock = parent::generate($controller, $mocks);
			if ($byPassSecurity) {
				$this->assertSecurityByPass($Mock);
			}
			return $Mock;
		}

		/**
		 * Assume that SecurityComponent was called
		 *
		 * @param $Controller
		 */
		public function assertSecurityBypass($Controller) {
			$Controller->Security->expects($this->any())
				->method('_validatePost')
				->will($this->returnValue(true));
			$Controller->Security->expects($this->any())
				->method('_validateCsrf')
				->will($this->returnValue(true));
		}

		protected function _logoutUser() {
			// if user is logged-in it should interfere with test runs
			if (isset($_COOKIE['SaitoPersistent'])) :
				unset($_COOKIE['SaitoPersistent']);
			endif;
			if (isset($_COOKIE['Saito'])) :
				unset($_COOKIE['Saito']);
			endif;
			if (isset($this->controller->Session) && !empty($this->controller->Session)) :
				$this->controller->Session->delete('Auth.User');
			endif;
		}

		public function endTest($method) {
			parent::endTest($method);
			$this->_logoutUser();
			$this->_unsetAjax();
		}

		public function setUp() {
			parent::setUp();
			$this->_logoutUser();
			$this->_unsetJson();
			Configure::write('Cache.disable', true);
			Configure::write('Config.language', 'eng');
		}

		public function tearDown() {
			Configure::write('Cache.disable', false);
			$this->_logoutUser();
			parent::tearDown();
		}

	}
