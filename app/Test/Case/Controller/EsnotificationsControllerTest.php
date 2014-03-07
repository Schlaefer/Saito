<?php

	App::uses('EsnotificationsController', 'Controller');
	App::uses('SaitoControllerTestCase', 'Lib');

/**
 * EsnotificationsController Test Case
 *
 */
class EsnotificationsControllerTest extends SaitoControllerTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.esnotification',
		'app.setting',
		'app.user',
		'app.user_online',
		'app.user_read',
		'app.entry',
		'app.category',
		'app.esevent',
		'app.upload'
	);

	public function testUnsubscribe() {
		$Esnotifications = $this->generate('Esnotifications', array(
				'models' => array(
						'Esnotification' => array('deleteNotificationWithId')
						)
				));
		$Esnotifications->Esnotification->expects($this->once())
				->method('deleteNotificationWithId')
				->with(4)
				->will($this->returnValue(true));

		$result = $this->testAction('/esnotifications/unsubscribe/4/token:4234/');
	}

	public function testUnsubscribeWrongToken() {
		$Esnotifications = $this->generate('Esnotifications', array(
				'models' => array(
						'Esnotification' => array('deleteNotificationWithId')
						)
				));
		$Esnotifications->Esnotification->expects($this->never())
				->method('deleteNotificationWithId');

		$this->setExpectedException('MethodNotAllowedException');
		$result = $this->testAction('/esnotifications/unsubscribe/4/token:12/');
	}

	public function testUnsubscribeNoToken() {
		$Esnotifications = $this->generate('Esnotifications', array(
				'models' => array(
						'Esnotification' => array('deleteNotificationWithId')
						)
				));
		$Esnotifications->Esnotification->expects($this->never())
				->method('deleteNotificationWithId');

		$this->setExpectedException('MethodNotAllowedException');
		$result = $this->testAction('/esnotifications/unsubscribe/4/');
	}

	public function testUnsubscribeNoEntry() {
		$Esnotifications = $this->generate('Esnotifications', array(
				'models' => array(
						'Esnotification' => array('deleteNotificationWithId')
						)
				));
		$Esnotifications->Esnotification->expects($this->never())
				->method('deleteNotificationWithId');

		$this->setExpectedException('NotFoundException');
		$result = $this->testAction('/esnotifications/unsubscribe/9999');
	}

}
