<?php

	App::uses('Controller', 'Controller');
	App::uses('View', 'View');
	App::uses('UserHHelper', 'View/Helper');

	class UserHHelperTest extends CakeTestCase {

		public $fixtures = array( 'app.setting' );

		public function testUserRank() {
			$_userranksShow = Configure::read('Saito.Settings.userranks_show');
			$_userranksRanks = Configure::read('Saito.Settings.userranks_ranks');

			Configure::write('Saito.Settings.userranks_show', '1');
			Configure::write('Saito.Settings.userranks_ranks',
				array(
					'10' => 'Castaway',
					'20' => 'Other',
					'30' => 'Dharma',
					'100' => 'Jacob'
				)
			);

			$this->UserH->beforeRender(null);

			$expected = 'Castaway';
			$result = $this->UserH->userRank(0);
			$this->assertEquals($expected, $result);

			$expected = 'Castaway';
			$result = $this->UserH->userRank(10);
			$this->assertEquals($expected, $result);

			$expected = 'Other';
			$result = $this->UserH->userRank(11);
			$this->assertEquals($expected, $result);

			$expected = 'Jacob';
			$result = $this->UserH->userRank(99);
			$this->assertEquals($expected, $result);

			$expected = 'Jacob';
			$result = $this->UserH->userRank(100);
			$this->assertEquals($expected, $result);

			$expected = 'Jacob';
			$result = $this->UserH->userRank(101);
			$this->assertEquals($expected, $result);

			Configure::write('Saito.Settings.userranks_show', $_userranksShow);
			Configure::write('Saito.Settings.userranks_ranks', $_userranksRanks);
		}

		public function setUp() {
			parent::setUp();
			$Controller = new Controller();
			$View = new View($Controller);
			$this->UserH = new UserHHelper($View);
		}

		public function tearDown() {
			parent::tearDown();
			unset($this->UserH);
			ClassRegistry::flush();
		}

	}

