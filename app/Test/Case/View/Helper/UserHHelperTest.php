<?php

	App::uses('Controller', 'Controller');
	App::uses('View', 'View');
	App::uses('UserHHelper', 'View/Helper');

	class UserHHelperTest extends CakeTestCase {

		public $fixtures = array( 'app.setting' );

		public function testUserRank() {

			$_userranks_show = Configure::read('Saito.Settings.userranks_show');
			$_userranks_ranks = Configure::read('Saito.Settings.userranks_ranks');

			Configure::write('Saito.Settings.userranks_show', '1');
			Configure::write('Saito.Settings.userranks_ranks', array(
					'10'=>'Castaway',
          '20'=>'Other',
          '30'=>'Dharma',
          '100'=>'Jacob')
      );

      $this->UserH->beforeRender(null);

			$expected = 'Castaway';
			$result = $this->UserH->userRank(0);
			$this->assertEqual($expected, $result);

			$expected = 'Castaway';
			$result = $this->UserH->userRank(10);
			$this->assertEqual($expected, $result);

			$expected = 'Other';
			$result = $this->UserH->userRank(11);
			$this->assertEqual($expected, $result);

			$expected = 'Jacob';
			$result = $this->UserH->userRank(99);
			$this->assertEqual($expected, $result);

			$expected = 'Jacob';
			$result = $this->UserH->userRank(100);
			$this->assertEqual($expected, $result);

			$expected = 'Jacob';
			$result = $this->UserH->userRank(101);
			$this->assertEqual($expected, $result);

			Configure::write('Saito.Settings.userranks_show', $_userranks_show);
			Configure::write('Saito.Settings.userranks_ranks', $_userranks_ranks);
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

?>