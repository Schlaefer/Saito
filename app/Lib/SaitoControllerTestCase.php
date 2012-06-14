<?php

  // load fixture
  App::uses('UserFixture', 'Fixture');

	// sets the FULL_BASE_URL for CLI tests
	if ( !defined('FULL_BASE_URL') ) {
		define('FULL_BASE_URL', 'http://cakephp.org/');
	}

	class SaitoControllerTestCase extends ControllerTestCase {

		/**
		 * Preserves $GLOBALS vars through PHPUnit test runs
		 *
		 * @see http://www.phpunit.de/manual/3.6/en/fixtures.html#fixtures.global-state
		 * @var array
		 */
		protected $backupGlobalsBlacklist = array(
				/*
				 * $GLOBALS['__STRINGPARSER_NODE_ID' is set in stringparser.class.php
				 * and must not cleared out
				 */
				'__STRINGPARSER_NODE_ID'
		);

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

    protected function _logoutUser() {
			if ( isset($this->controller->Session) && !empty($this->controller->Session) ) :
				$this->controller->Session->delete('Auth.User');
			endif;
    }

		public function setUp() {
			parent::setUp();

			Configure::write('Cache.disable', true);
      Configure::write('Config.language', 'eng');
		}

		public function tearDown() {
      $this->_logoutUser();

			Configure::write('Cache.disable', false);
			parent::tearDown();
		}

	}

?>