<?php
App::uses('View', 'View');
App::uses('Helper', 'View');
App::uses('TimeHHelper', 'View/Helper');

/**
 * TimeHHelper Test Case
 *
 */
class TimeHHelperTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$View = new View();
		$this->TimeH = new TimeHHelper($View);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->TimeH);

		parent::tearDown();
	}

/**
 * testTimezoneOptions method
 *
 * @return void
 */
	public function testTimezoneOptions() {
	}

/**
 * testFormatTime method
 *
 * @return void
 */
	public function testFormatTime() {
	}

/**
 * testTimeAgoInWordsFuzzy method
 *
 * @return void
 */
	public function testTimeAgoInWordsFuzzy() {
		// first run, no output
		$time = '2010-01-01 19:00:01';
		$result = $this->TimeH->timeAgoInWordsFuzzy(
			$time,
			array(
				'conversationCoolOff' => 240
			)
		);
		$this->assertFalse($result);

		// should not output because of conversationCoolOff of 4 min
		$time = '2010-01-01 19:00:00';
		$this->TimeH->timeAgoInWordsFuzzy($time);
		$this->assertFalse($result);

		// should output because of conversationCoolOff of 4 min
		$time = '2010-01-01 18:56:01';
		$result = $this->TimeH->timeAgoInWordsFuzzy($time);
		$this->assertFalse($result);

		// test setup
		$time = '2010-01-01 18:56:00';
		$this->TimeH->timeAgoInWordsFuzzy($time);

		// should output because of conversationCoolOff of 4 min
		$time = '2010-01-01 18:51:00';
		$result = $this->TimeH->timeAgoInWordsFuzzy($time);
		$this->assertNotEmpty($result);

		// should output because of conversationCoolOff of 4 min
		$time = '2010-01-01 18:40:00';
		$result = $this->TimeH->timeAgoInWordsFuzzy($time);
		$this->assertNotEmpty($result);
	}

}
