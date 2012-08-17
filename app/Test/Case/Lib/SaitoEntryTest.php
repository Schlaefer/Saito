<?php

	App::uses('Component', 'Controller');
	App::uses('ComponentCollection', 'Controller');
	App::uses('SaitoEntry', 'Lib');

	class SaitoEntryTest extends CakeTestCase {

		public function testIsEditingForbiddenSuccess() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c",
									time() - (Configure::read('Saito.Settings.edit_period') * 60 ) + 1),
							'locked'	 => 0,
					)
			);
			$user = array(
					'id'				 => 1,
					'user_type'	 => 'user',
			);
			$result = $this->SaitoEntry->isEditingForbidden($entry, $user);
			$this->assertFalse($result);
		}

		public function testIsEditingForbiddenEmptyUser() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c",
									time() - (Configure::read('Saito.Settings.edit_period') * 60 ) + 1),
							'locked'	 => 0,
					)
			);
			$user = null;
			$result = $this->SaitoEntry->isEditingForbidden($entry, $user);
			$this->assertTrue($result);
		}


		public function testIsEditingForbiddenAnon() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c", time()),
					)
			);
			$user = array(
					'id'				 => null,
					'user_type'	 => 'anon',
			);
			$result = $this->SaitoEntry->isEditingForbidden($entry, $user);
			$this->assertTrue($result);
		}

		public function testIsEditingForbiddenWrongUser() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c", time()),
					)
			);
			$user = array(
					'id'				 => 2,
					'user_type'	 => 'user',
			);
			$result = $this->SaitoEntry->isEditingForbidden($entry, $user);
			$this->assertEqual($result, 'user');
		}

		public function testIsEditingForbiddenToLate() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c",
									time() - (Configure::read('Saito.Settings.edit_period') * 60 ) - 1),
					)
			);
			$user = array(
					'id'				 => 1,
					'user_type'	 => 'user',
			);
			$result = $this->SaitoEntry->isEditingForbidden($entry, $user);
			$this->assertEqual($result, 'time');
		}

		public function testIsEditingForbiddenLocked() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c", time()),
							'locked'	 => 1,
					)
			);
			$user = array(
					'id'				 => 1,
					'user_type'	 => 'user',
			);
			$result = $this->SaitoEntry->isEditingForbidden($entry, $user);
			$this->assertEqual($result, 'locked');
		}

		public function testIsEditingForbiddenModToLateNotFixed() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c",
									time() - (Configure::read('Saito.Settings.edit_period') * 60 ) - 1),
							'fixed'		 => false,
					)
			);
			$user = array(
					'id'				 => 1,
					'user_type'	 => 'mod',
			);
			$result = $this->SaitoEntry->isEditingForbidden($entry, $user);
			$this->assertEqual($result, 'time');
		}

		public function testIsEditingForbiddenModToLateFixed() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c",
									time() - (Configure::read('Saito.Settings.edit_period') * 60 ) - 1),
							'fixed'		 => true,
					)
			);
			$user = array(
					'id'				 => 1,
					'user_type'	 => 'mod',
			);
			$result = $this->SaitoEntry->isEditingForbidden($entry, $user);
			$this->assertFalse($result);
		}

		public function testIsEditingForbiddenAdminToLateNotFixed() {
			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c",
									time() - (Configure::read('Saito.Settings.edit_period') * 60 ) - 1),
							'fixed'		 => false,
					)
			);
			$user = array(
					'id'				 => 1,
					'user_type'	 => 'admin',
			);
			$result = $this->SaitoEntry->isEditingForbidden($entry, $user);
			$this->assertFalse($result);
		}

		public function testIsEditingForbiddenMockUserType() {
			$this->SaitoEntry = $this->getMock('SaitoEntry', array('isEditingForbidden'),
					array(new ComponentCollection()));

			$entry = array(
					'Entry' => array(
							'user_id'	 => 1,
							'time'		 => strftime("%c",
									time() - (Configure::read('Saito.Settings.edit_period') * 60 ) + 1),
							'locked'	 => 0,
					)
			);
			$user = array(
					'id'				 => 1,
					'user_type'	 => 'admin',
			);
			$user_mock = $user;
			$user_mock['user_type'] = 'user';

			$this->SaitoEntry->expects($this->once())
					->method('isEditingForbidden')
					->with($entry, $user_mock);
			$this->SaitoEntry->isEditingForbiddenMockUserType($entry, $user, 'user');
		}

		public function setUp() {
			parent::setUp();
			$this->SaitoEntry = new SaitoEntry(new ComponentCollection());
		}

		public function tearDown() {
			parent::tearDown();
			unset($this->SaitoEntry);
		}

	}

