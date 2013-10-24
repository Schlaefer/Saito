<?php
App::uses('ShoutsController', 'Controller');

/**
 * ShoutsController Test Case
 *
 */
class ShoutsControllerTest extends ControllerTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.shout',
		'app.user',
		'app.user_online',
		'app.bookmark',
		'app.entry',
		'app.category',
		'app.esevent',
		'app.esnotification',
		'app.upload',
		'app.setting'
	);

	public function testDummy() {
	}

}
