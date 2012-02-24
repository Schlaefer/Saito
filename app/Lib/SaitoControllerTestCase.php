<?php

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
			if ( isset($this->controller->Session) && !empty($this->controller->Session) ) :
				$this->controller->Session->delete('Auth.User');
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

			$this->controller->Session->write('Auth.User', $records[$id - 1]);
		}

		public function tearDown() {
			if ( isset($this->controller->Session) ) :
				$this->controller->Session->delete('Auth.User');
			endif;

			parent::tearDown();
		}

	}

?>