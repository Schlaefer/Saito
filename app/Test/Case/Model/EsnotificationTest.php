<?php
App::uses('Esnotification', 'Model');

/**
 * Esnotification Test Case
 *
 */
class EsnotificationTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.esnotification',
		'app.user',
		'app.user_online',
		'app.entry',
		'app.category',
		'app.esevent',
		'app.upload'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Esnotification = ClassRegistry::init('Esnotification');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Esnotification);

		parent::tearDown();
	}

/**
 * testDeleteAllFromUser method
 *
 * @return void
 */
	public function testDeleteAllFromUser() {
		$eventsBeforeDeletion = $this->Esnotification->Esevent->find('count');
		$userEntriesBeforeDeletion = $this->Esnotification->find('count', array('conditions' => array('user_id' => 1)));
		$allEntriesBeforeDeletion = $this->Esnotification->find('count');

		$this->Esnotification->deleteAllFromUser(1);

		$eventsAfterDeletion = $this->Esnotification->Esevent->find('count');
		$userEntriesAfterDeletion = $this->Esnotification->find('count', array('conditions' => array('user_id' => 1)));
		$allEntriesAfterDeletion = $this->Esnotification->find('count');

		$this->assertEqual($eventsBeforeDeletion, $eventsAfterDeletion);
		$this->assertEqual($allEntriesAfterDeletion, $allEntriesBeforeDeletion-$userEntriesBeforeDeletion);
		$this->assertEqual($userEntriesAfterDeletion, 0);

	}

}
