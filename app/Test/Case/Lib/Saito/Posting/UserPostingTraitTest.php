<?php

	use Saito\User\SaitoUser;

	class UserPostingTraitClassMock extends \Saito\Posting\Posting {

		public function __construct() {
		}

		public function set($key, $val) {
			if (strpos($key, '_') === 0) {
				$this->{$key} = $val;
			} elseif ($key === lcfirst($key)) {
				$this->_rawData['Entry'][$key] = $val;
			} else {
				$this->_rawData[$key] = $val;
			}
		}

	}

	class UserPostingTraitTest extends CakeTestCase {

		public $editPeriod = 20;

		public function testIsAnsweringForbidden() {
			$this->Mock->set('locked', 0);
			$result = $this->Mock->isAnsweringForbidden();
			$expected = false;
			$this->assertSame($result, $expected);

			$this->Mock->set('locked', '0');
			$result = $this->Mock->isAnsweringForbidden();
			$expected = false;
			$this->assertSame($result, $expected);

			$this->Mock->set('locked', false);
			$result = $this->Mock->isAnsweringForbidden();
			$expected = false;
			$this->assertSame($result, $expected);
		}

		public function testIsEditingForbiddenSuccess() {
			$entry = [
				'user_id' => 1,
				'time' => strftime("%c", time() - ($this->editPeriod * 60) + 1),
				'locked' => 0
			];
			$this->Mock->set('Entry', $entry);

			$user = ['id' => 1, 'user_type' => 'user'];
			$this->Mock->set('_CU', new SaitoUser($user));

			$result = $this->Mock->isEditingAsCurrentUserForbidden();
			$this->assertFalse($result);
		}

		public function testIsEditingForbiddenEmptyUser() {
			$entry = [
				'user_id' => 1,
				'time' => strftime("%c",
					time() - ($this->editPeriod * 60) + 1),
				'locked' => 0,
			];
			$this->Mock->set('Entry', $entry);
			$this->Mock->set('_CU', new SaitoUser);
			$result = $this->Mock->isEditingAsCurrentUserForbidden();
			$this->assertTrue($result);
		}

		public function testIsEditingForbiddenAnon() {
			$entry = [
				'user_id' => 1,
				'time' => strftime("%c", time()),
			];
			$this->Mock->set('Entry', $entry);
			$user = [
				'id' => null,
				'user_type' => 'anon',
			];
			$this->Mock->set('_CU', new SaitoUser($user));
			$result = $this->Mock->isEditingAsCurrentUserForbidden();
			$this->assertTrue($result);
		}

		public function testIsEditingForbiddenWrongUser() {
			$entry = [
				'user_id' => 1,
				'time' => strftime("%c", time()),
			];
			$this->Mock->set('Entry', $entry);
			$user = [
				'id' => 2,
				'user_type' => 'user',
			];
			$this->Mock->set('_CU', new SaitoUser($user));
			$result = $this->Mock->isEditingAsCurrentUserForbidden();
			$this->assertEquals($result, 'user');
		}

		public function testIsEditingForbiddenToLate() {
			$editPeriod = 20;
			Configure::write('Saito.Settings.edit_period', $editPeriod);
			$entry = [
				'user_id' => 1,
				'locked' => false,
				'time' => strftime( "%c", time() - ($this->editPeriod * 60) - 1)
			];
			$this->Mock->set('Entry', $entry);
			$user = [
				'id' => 1,
				'user_type' => 'user',
			];
			$this->Mock->set('_CU', new SaitoUser($user));
			$result = $this->Mock->isEditingAsCurrentUserForbidden();
			$this->assertEquals($result, 'time');
		}

		public function testIsEditingForbiddenLocked() {
			$entry = [
				'user_id' => 1,
				'time' => strftime("%c", time()),
				'locked' => 1,
			];
			$this->Mock->set('Entry', $entry);
			$user = [
				'id' => 1,
				'user_type' => 'user',
			];
			$this->Mock->set('Entry', $entry);
			$this->Mock->set('_CU', new SaitoUser($user));
			$result = $this->Mock->isEditingAsCurrentUserForbidden();
			$this->assertEquals($result, 'locked');
		}

		public function testIsEditingForbiddenModToLateNotFixed() {
			$entry = [
				'user_id' => 1,
				'time' => strftime("%c",
					time() - ($this->editPeriod * 60) - 1),
				'fixed' => false,
			];
			$user = [
				'id' => 1,
				'user_type' => 'mod',
			];
			$this->Mock->set('Entry', $entry);
			$this->Mock->set('_CU', new SaitoUser($user));
			$result = $this->Mock->isEditingAsCurrentUserForbidden();
			$this->assertEquals($result, 'time');
		}

		public function testIsEditingForbiddenModToLateFixed() {
			$entry = [
				'user_id' => 1,
				'time' => strftime("%c",
					time() - (Configure::read('Saito.Settings.edit_period') * 60) - 1),
				'fixed' => true,
			];
			$user = [
				'id' => 1,
				'user_type' => 'mod',
			];
			$this->Mock->set('Entry', $entry);
			$this->Mock->set('_CU', new SaitoUser($user));
			$result = $this->Mock->isEditingAsCurrentUserForbidden();
			$this->assertFalse($result);
		}

		public function testIsEditingForbiddenAdminToLateNotFixed() {
			$entry = [
				'user_id' => 1,
				'time' => strftime("%c",
					time() - ($this->editPeriod * 60) - 1),
				'fixed' => false,
			];
			$user = [
				'id' => 1,
				'user_type' => 'admin',
			];
			$this->Mock->set('Entry', $entry);
			$this->Mock->set('_CU', new SaitoUser($user));
			$result = $this->Mock->isEditingAsCurrentUserForbidden();
			$this->assertFalse($result);
		}

		public function setUp() {
			$this->editPeriodGlob = Configure::read('Saito.Settings.edit_period');
			Configure::write('Saito.Settings.edit_period', $this->editPeriod);
			$this->Mock = new UserPostingTraitClassMock();
		}

		public function tearDown() {
			unset($this->Mock);
			Configure::write('Saito.Settings.edit_period', $this->editPeriodGlob);
		}

	}
