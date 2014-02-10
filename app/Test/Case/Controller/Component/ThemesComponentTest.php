<?php

	App::uses('Controller', 'Controller');
	App::uses('ComponentCollection', 'Controller');
	App::uses('CurrentUserComponent', 'Controller/Component');

	App::uses('Component', 'Controller');
	App::uses('ThemesComponent', 'Controller/Component');

	class ThemesComponentControllerMock extends Controller {

		public $CurrentUser;

	}

	class ThemesComponentMock extends ThemesComponent {

		public function themeDirs() {
			return ['Default', 'Ixi', 'Paz'];
		}

	}

	/**
	 * ThemesComponent Test Case
	 *
	 */
	class ThemesComponentTest extends CakeTestCase {

		/**
		 * setUp method
		 *
		 * @return void
		 */
		public function setUp() {
			parent::setUp();
			$Collection = new ComponentCollection();
			$this->Themes = new ThemesComponentMock($Collection);

			$this->ThemeConfig = Configure::read('Saito.themes');

			// controller setup
			$this->Controller = $this->getMock('ThemesComponentControllerMock');
			$this->Controller->CurrentUser = $this->getMockBuilder('CurrentUserComponent')
					->disableOriginalConstructor()
					->getMock('CurrentUserComponent');
			$this->Themes->initialize($this->Controller);
		}

		/**
		 * tearDown method
		 *
		 * @return void
		 */
		public function tearDown() {
			unset($this->Themes);
			unset($this->Controller);
			Configure::write('Saito.themes', $this->ThemeConfig);

			parent::tearDown();
		}

		public function testSetThemeString() {
			$_name = 'NameOfTheme';
			$_r = $this->Themes->theme($_name);
			$this->assertEqual($_r, $_name);
			$this->assertEqual($this->Themes->theme(), $_name);
		}

		/**
		 * testSetDefault method
		 *
		 * @return void
		 */
		public function testSetDefault() {
			$config = [
					'default' => 'Default',
					'available' => [
							'all' => '*',
							1 => 'Paz'
					]
			];

			$this->Themes->theme($config);
			$this->Themes->setDefault();
			$this->assertEqual($this->Themes->theme(), 'Default');
		}

		public function testGetAvailableOneTheme() {
			$this->Controller->CurrentUser->expects($this->once())
					->method('getId')
					->will($this->returnValue(1));

			$config = [
				'default' => 'Default'
			];

			$this->Themes->theme($config);
			$_r = $this->Themes->getAvailable();
			$this->assertEqual($_r, ['Default']);
		}

		public function testGetAvailableAll() {
			$this->Controller->CurrentUser->expects($this->once())
					->method('getId')
					->will($this->returnValue(0));

			$config = [
					'default' => 'Default',
					'available' => [
							'all' => '*',
					]
			];

			$this->Themes->theme($config);
			$_r = $this->Themes->getAvailable();
			$this->assertEqual(array_values($_r), ['Default', 'Ixi']);
		}

		public function testGetAvailableUserNotAllowed() {
			$this->Controller->CurrentUser->expects($this->once())
					->method('getId')
					->will($this->returnValue(2));

			$config = [
					'default' => 'Default',
					'available' => [
							'all' => ['Ixi'],
							'user' => [1 => ['Paz']]
					]
			];

			$this->Themes->theme($config);
			$_r = $this->Themes->getAvailable();
			$this->assertEqual(array_values($_r), ['Default', 'Ixi']);
		}

		public function testGetAvailableUserAllowed() {
			$this->Controller->CurrentUser->expects($this->once())
					->method('getId')
					->will($this->returnValue(1));

			$config = [
					'default' => 'Default',
					'available' => [
							'users' => [1 => ['Paz']]
					]
			];

			$this->Themes->theme($config);
			$_r = $this->Themes->getAvailable();
			$this->assertEqual(array_values($_r), ['Default']);
		}

		public function testThemeDirs() {
			$Collection = new ComponentCollection();
			$this->Themes = new ThemesComponent($Collection);
			$result = $this->Themes->themeDirs();
			$this->assertEqual($result, ['Paz']);
		}

	}
