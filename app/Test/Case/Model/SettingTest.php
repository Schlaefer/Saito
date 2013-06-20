<?php

	App::uses('Setting', 'Model');

	class SettingTest extends CakeTestCase {

		public $fixtures = array( 'app.setting' );

		protected $settingsCompact = array(
			'forum_name'          => 'macnemo',
			'forum_email'         => 'forum_email@example.com',
			'autolink'            => '1',
			'userranks_ranks'     => array(
				'10'  => 'Castaway',
				'20'  => 'Other',
				'30'  => 'Dharma',
				'100' => 'Jacob'
			),
			'quote_symbol'        => '>',
			'smilies'             => 1,
			'topics_per_page'     => 20,
			'timezone'            => 'UTC',
			'block_user_ui'       => 1,
			'subject_maxlength'   => 100,
			'tos_enabled'         => 1,
			'tos_url'             => 'http://example.com/tos-url.html/',
			'thread_depth_indent' => '25'
		);

		public function testAfterSave() {

			$data = array(
					'value' => 'test',
			);

			$this->Setting->id = 'forum_name';
			$this->Setting->save($data);

			$result = $this->Setting->getSettings();
			$expected = array_merge($this->settingsCompact,
					array( 'forum_name' => 'test', 'autolink' => 1 ));
			$this->assertEqual($result, $expected);
		}

		public function testGetSettings() {
			$result = $this->Setting->getSettings();
			$expected = $this->settingsCompact;
			$this->assertEqual($result, $expected);
		}

		/**
		 *
		 *
		 * preset must force a refresh
		 */
		public function testLoadWithPreset() {
			$this->Setting->load();

			$preset = array(
					'lock'								 => 'hatch',
					'timezone'						 => 'island',
			);
			$this->Setting->load($preset);
			$result = Configure::read('Saito.Settings');
			$expected = $this->settingsCompact;
			$expected['lock'] = 'hatch';
			$expected['timezone'] = 'island';
			$this->assertEqual($result, $expected);
		}

		public function testLoad() {

			Configure::write('Saito.Settings', null);
			$this->Setting->load();
			$result = Configure::read('Saito.Settings');
			$expected = $this->settingsCompact;
			$this->assertEqual($result, $expected);

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

?>