<?php

	namespace Saito\Test;

	/*
	fixes early output in PHPUnit 3.7:

		$this->printer->write(
			PHPUnit_Runner_Version::getVersionString() . "\n\n"
		);

	that prevents session setting in HTML test-run
	*/
	ob_start();

	// load fixture
	\App::uses('UserFixture', 'Fixture');

	// sets the FULL_BASE_URL for CLI tests
	if (!defined('FULL_BASE_URL')) {
		define('FULL_BASE_URL', 'http://cakephp.org/');
	}
	\Configure::write('App.fullBaseURL', FULL_BASE_URL);


	class ControllerTestCase extends \ControllerTestCase {

		use AssertTrait;

		/**
		 * @var array cache environment variables
		 */
		protected $_env = [];

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

		protected function _setUserAgent($agent) {
			if (isset($this->_env['HTTP_USER_AGENT'])) {
				$this->_env['HTTP_USER_AGENT'] = $_ENV['HTTP_USER_AGENT'];
			}
			$_ENV['HTTP_USER_AGENT'] = $agent;
		}

		protected function _unsetUserAgent() {
			if (isset($this->_env['HTTP_USER_AGENT'])) {
				$_ENV['HTTP_USER_AGENT'] = $this->_env('HTTP_USER_AGENT');
			} else {
				unset($_ENV['HTTP_USER_AGENT']);
			}
		}

		protected function _loginUser($id) {
			// see: http://stackoverflow.com/a/10411128/1372085
			$this->_logoutUser();
			$userFixture = new \UserFixture();
			$users = $userFixture->records;

			$this->controller->Session->write('Auth.User', $users[$id - 1]);
		}

		protected function _debugEmail() {
			\Configure::write('Saito.Debug.email', true);
		}

		protected function _resetEmail() {
			\Configure::write('Saito.Debug.email', false);
		}

		public function assertRedirectedTo($url = '') {
			$this->assertEquals(
				$this->headers['Location'],
				\Router::fullBaseUrl() . $this->controller->request->webroot . $url
			);
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

		protected function _notImplementedOnDatasource($name) {
			$mc = $this->controller->modelClass;
			$Ds = $this->controller->{$mc}->getDataSource();
			$this->_DsName = get_class($Ds);
			if ($this->_DsName === $name) {
				$this->markTestIncomplete("Datasource is $name.");
			}
		}

		public function endTest($method) {
			parent::endTest($method);
			$this->_logoutUser();
			$this->_unsetAjax();
		}

		public function setUp() {
			parent::setUp();
			$this->_logoutUser();
			$this->_unsetAjax();
			$this->_unsetJson();
			$this->_debugEmail();
			\Configure::write('Cache.disable', true);
			\Configure::write('Config.language', 'eng');
		}

		public function tearDown() {
			\Configure::write('Cache.disable', false);
			$this->_unsetUserAgent();
			$this->_resetEmail();
			$this->_logoutUser();
			parent::tearDown();
		}

	}


