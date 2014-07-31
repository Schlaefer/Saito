<?php

	App::uses('Setting', 'Model');

	class SettingTest extends CakeTestCase {

		public $fixtures = array('app.setting');

		protected $_settingsCompact = array(
			'forum_name' => 'macnemo',
			'forum_email' => 'forum_email@example.com',
			'email_contact' => 'contact@example.com',
			'email_register' => 'register@example.com',
			'email_system' => 'system@example.com',
			'autolink' => '1',
			'userranks_ranks' => array(
				'10' => 'Castaway',
				'20' => 'Other',
				'30' => 'Dharma',
				'100' => 'Jacob'
			),
			'quote_symbol' => '>',
			'smilies' => 1,
			'topics_per_page' => 20,
			'timezone' => 'UTC',
			'block_user_ui' => 1,
			'subject_maxlength' => 40,
			'tos_enabled' => 1,
			'tos_url' => 'http://example.com/tos-url.html/',
			'thread_depth_indent' => '25',
			'edit_delay' => 180,
			'edit_period' => '20',
			'shoutbox_enabled' => true,
			'shoutbox_max_shouts' => 5
		);

		public function testFillOptionalMailAddresses() {
			$this->Setting = $this->getMockForModel('Setting', ['_compactKeyValue']);

			$returnValue = [
				'edit_delay' => 0,
				'forum_email' => 'foo@bar.com',
				'userranks_ranks' => ''
			];

			$this->Setting->expects($this->once())
				->method('_compactKeyValue')
				->will($this->returnValue($returnValue));
			$result = $this->Setting->getSettings();

			$expected = 'foo@bar.com';
			$this->assertEquals($expected, $result['forum_email']);
			$this->assertEquals($expected, $result['email_contact']);
			$this->assertEquals($expected, $result['email_register']);
			$this->assertEquals($expected, $result['email_system']);
		}

		public function testAfterSave() {
			$data = array(
					'value' => 'test',
			);

			$this->Setting->id = 'forum_name';
			$this->Setting->save($data);

			$result = $this->Setting->getSettings();
			$expected = array_merge($this->_settingsCompact,
					array( 'forum_name' => 'test', 'autolink' => 1 ));
			$this->assertEquals($result, $expected);
		}

		public function testGetSettings() {
			$result = $this->Setting->getSettings();
			$expected = $this->_settingsCompact;
			$this->assertEquals($result, $expected);
		}

		/**
		 *
		 *
		 * preset must force a refresh
		 */
		public function testLoadWithPreset() {
			$this->Setting->load();

			$preset = array(
				'lock' => 'hatch',
				'timezone' => 'island',
			);
			$this->Setting->load($preset);
			$result = Configure::read('Saito.Settings');
			$expected = $this->_settingsCompact;
			$expected['lock'] = 'hatch';
			$expected['timezone'] = 'island';
			$this->assertEquals($result, $expected);
		}

		public function testLoad() {
			Configure::write('Saito.Settings', null);
			$this->Setting->load();
			$result = Configure::read('Saito.Settings');
			$expected = $this->_settingsCompact;
			$this->assertEquals($result, $expected);
		}

		public function setUp() {
			parent::setUp();
			$this->Setting = ClassRegistry::init('Setting');
		}

		public function tearDown() {
			$this->Setting->clearCache();
			unset($this->Setting);
			parent::tearDown();
		}

	}
