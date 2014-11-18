<?php

    // @todo 3.0 remove

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
			\Configure::write('Saito.language', 'eng');
		}

		public function tearDown() {
			\Configure::write('Cache.disable', false);
			$this->_resetEmail();
			$this->_logoutUser();
			parent::tearDown();
		}

	}


