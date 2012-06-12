<?php

	App::uses('Setting', 'Model');

	class SettingTest extends CakeTestCase {

		public $fixtures = array( 'app.setting' );

		protected $settingsCompact = array(
				'forum_name' => 'macnemo',
				'autolink' => '1',
				'userranks_ranks' => array(
					'10'=>'Castaway',
          '20'=>'Other',
          '30'=>'Dharma',
          '100'=>'Jacob'),
				'smilies' => 1,
				'topics_per_page' => 20,
				'timezone'				=> 'UTC',
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

		public function testLoad() {

			//* test loading settings from DB
			$result = $this->Setting->load();
			$expected = $this->settingsCompact;
			$this->assertEqual($result, $expected);

			//* test writing settings into app-config
			Configure::write('Saito.Settings', NULL);
			$this->Setting->load();
			$result = Configure::read('Saito.Settings');
			$expected = $this->settingsCompact;
			$this->assertEqual($result, $expected);

			//* test caching
			$debugState = Configure::read('debug');
			Configure::write('debug', 0);
			$cacheDisable = Configure::read('Cache.disable');
			Configure::write('Cache.disable', false);

			$this->Setting->load();
			$result = Cache::read('Saito.Settings');

			Cache::delete('Saito.Settings');
			Configure::write('debug', $debugState);
			Configure::write('Cache.disable', $cacheDisable);

			$expected = $this->settingsCompact;
			$this->assertEqual($result, $expected);
		}

		public function setUp() {
			$this->Setting = ClassRegistry::init('Setting');
		}

	}

?>