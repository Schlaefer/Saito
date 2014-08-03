<?php

	App::uses('LastRefreshDatabase', 'Lib/SaitoUser/LastRefresh');
	App::uses('CurrentUserComponent', 'Controller/Component');

	class LastRefreshDatabaseTest extends CakeTestCase {

		/**
		 * @var CurrentUserComponent;
		 */
		public $CurrentUser;

		public function setUp() {
			parent::setUp();
			$Collection = new ComponentCollection();
			$this->CurrentUser = new CurrentUserComponent($Collection);
			$this->LastRefresh = new LastRefreshDatabase($this->CurrentUser);
		}

		/**
		 * Tests that a newly registered users sees everything as new
		 */
		public function testIsNewerThanForNewUsers() {
			$userData = ['id' => 1, 'last_refresh' => null];
			$this->CurrentUser->setSettings($userData);

			$this->assertTrue($this->LastRefresh->isNewerThan(time()));
			$this->assertTrue($this->LastRefresh->isNewerThan(date('Y-m-d h:i:s', time())));
		}

		/**
		 * tests entry is newer than last refresh
		 */
		public function testIsNewerThanTrue() {
			$time = time();
			$lastRefresh = date('Y-m-d H:i:s', $time + 10);
			$userData = ['id' => 1, 'last_refresh' => $lastRefresh];
			$this->CurrentUser->setSettings($userData);

			$this->assertTrue($this->LastRefresh->isNewerThan($time));
			$this->assertTrue($this->LastRefresh->isNewerThan(date('Y-m-d H:i:s', $time)));
		}

		/**
		 * tests entry is older than last refresh
		 */
		public function testIsNewerThanFalse() {
			$time = time();
			$lastRefresh = date('Y-m-d H:i:s', $time - 10);
			$userData = ['id' => 1, 'last_refresh' => $lastRefresh];
			$this->CurrentUser->setSettings($userData);

			$this->assertFalse($this->LastRefresh->isNewerThan($time));
			$this->assertFalse($this->LastRefresh->isNewerThan(date('Y-m-d H:i:s', $time)));
		}

		public function tearDown() {
			unset($this->CurrentUser);
			unset($this->LastRefresh);
			parent::tearDown();
		}

	}
