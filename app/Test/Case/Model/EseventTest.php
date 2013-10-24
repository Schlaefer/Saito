<?php

	App::uses('Esevent', 'Model');

	class EseventMock extends Esevent {

		public $useTable = 'esevents';

		public $alias = 'Esevent';

		public function transferSubjectForEventType($oldSubject, $newSubject, $subjectType) {
			$this->_eventTypes['testCase'] = 3;
			$this->_subjectTypes['entry'] = array(1, 3);
			return parent::transferSubjectForEventType($oldSubject, $newSubject, $subjectType);
		}

		public function getEventSet($params) {
			return $this->_getEventSet($params);
		}

	}

	/**
	 * Esevent Test Case
	 *
	 */
	class EseventTest extends CakeTestCase {

		/**
		 * Fixtures
		 *
		 * @var array
		 */
		public $fixtures = array(
				'app.esevent',
				'app.esnotification',
				'app.user',
				'app.user_online',
				'app.entry',
				'app.category',
				'app.upload'
		);

		/**
		 * setUp method
		 *
		 * @return void
		 */
		public function setUp() {
			parent::setUp();
			$this->Esevent = ClassRegistry::init('EseventMock');
		}

		/**
		 * tearDown method
		 *
		 * @return void
		 */
		public function tearDown() {
			unset($this->Esevent);

			parent::tearDown();
		}

		public function testTransferSubjectForEventType() {
			$oldSubject = 1;
			$newSubject = 2;
			$entryType = 'entry';

			$countNotificationsBefore = $this->Esevent->Esnotification->find('count');
			$countOldEventsBefore = $this->Esevent->find('count',
				array(
					'conditions' => array(
						'subject' => $oldSubject,
						'event' => array(1, 3),
					)
				));
			$this->assertGreaterThan(0, $countOldEventsBefore);

			$countUserNotificationsBefore = $this->Esevent->Esnotification->User->find('count',
				array(
					'contain' => array('Esnotification'),
					'conditions' => array('id' => 1)
				));
			$this->assertGreaterThan(0, $countUserNotificationsBefore);

			$this->Esevent->transferSubjectForEventType($oldSubject,
				$newSubject,
				$entryType);

			$notifications1 = $this->Esevent->find('all',
				array(
					'conditions' => array(
						'subject' => $newSubject,
						'event' => 1,
					)
				));
			$notifications3 = $this->Esevent->find('all',
				array(
					'conditions' => array(
						'subject' => $newSubject,
						'event' => 3,
					)
				));
			$notificationsAfter = array(
				1 => $notifications1[0]['Esnotification'],
				3 => $notifications3[0]['Esnotification'],
			);
			$expected = array(
				(int)1 => array(
					(int)0 => array(
						'id' => '1',
						'user_id' => '1',
						'esevent_id' => '3',
						'esreceiver_id' => '1',
						'deactivate' => 1234,
					),
					(int)1 => array(
						'id' => '2',
						'user_id' => '1',
						'esevent_id' => '3',
						'esreceiver_id' => '2',
						'deactivate' => 2234,
					),
					(int)2 => array(
						'id' => '3',
						'user_id' => '3',
						'esevent_id' => '3',
						'esreceiver_id' => '1',
						'deactivate' => 3234,
					),
					(int)3 => array(
						'id' => '7',
						'user_id' => '4',
						'esevent_id' => '3',
						'esreceiver_id' => '1',
						'deactivate' => '7234'
					)
				),
				(int)3 => array(
					(int)0 => array(
						'id' => '4',
						'user_id' => '3',
						'esevent_id' => '5',
						'esreceiver_id' => '1',
						'deactivate' => 4234,
					),
					(int)1 => array(
						'id' => '5',
						'user_id' => '2',
						'esevent_id' => '5',
						'esreceiver_id' => '1',
						'deactivate' => 5234,
					),
				)
			);

			$this->assertEqual($expected, $notificationsAfter);

			// old events should be gone
			$countOldEventsAfter = $this->Esevent->find('count',
				array(
					'contain' => array('Esnotification'),
					'conditions' => array(
						'subject' => $oldSubject,
						'event' => array(1, 3),
					)
				));
			$this->assertEqual($countOldEventsAfter, 0);

			// user should still have all his notifications
			$countUserNotificationsAfter = $this->Esevent->Esnotification->User->find('count',
				array(
					'contain' => array('Esnotification'),
					'conditions' => array('id' => 1)
				));
			$this->assertEqual($countUserNotificationsBefore,
					$countUserNotificationsAfter);

			// no notification should be lost
			$countNotificationsAfter = $this->Esevent->Esnotification->find('count');
			$this->assertEqual($countNotificationsBefore, $countNotificationsAfter);
		}

		/**
		 * testNotifyUserOnEvents method
		 *
		 * @return void
		 */
		public function testNotifyUserOnEvents() {
			$data = array(
				array(
					'subject' => 1,
					'event' => 'Model.Entry.replyToThread',
					'receiver' => 'EmailNotification',
					'set' => 1,
				),
				array(
					'subject' => 1,
					'event' => 'Model.Entry.replyToEntry',
					'receiver' => 'EmailNotification',
					'set' => 1,
				),
				array(
					'subject' => 2,
					'event' => 'Model.Entry.replyToEntry',
					'receiver' => 'EmailNotification',
					'set' => 0,
				),
			);

			$params = array(
				0 => array(
					'subject' => 1,
					'event' => 2,
					'user_id' => 1,
					'receiver' => 1,
				),
				1 => array(
					'subject' => 1,
					'event' => 1,
					'user_id' => 1,
					'receiver' => 1,
				),
				2 => array(
					'subject' => 2,
					'event' => 1,
					'user_id' => 1,
					'receiver' => 1,
				),
			);

			$this->Esevent = $this->getMock('Esevent',
					array('setNotification', 'deleteNotification'),
					array(false, 'esevents', 'test')
			);
			$this->Esevent->expects($this->at(0))
					->method('setNotification')
					->with($params[0]);
			$this->Esevent->expects($this->at(1))
					->method('setNotification')
					->with($params[1]);
			$this->Esevent->expects($this->once())
					->method('deleteNotification')
					->with($params[2]);
			$this->Esevent->notifyUserOnEvents(1, $data);
		}

		/**
		 * testSetNotification method
		 *
		 * @return void
		 */
		public function testSetNotification() {
			$data = array(
				0 => array(
					'subject' => 1000,
					'event' => 1,
					'user_id' => 1000,
					'receiver' => 1,
				),
				1 => array(
					'subject' => 1000,
					'event' => 1,
					'user_id' => 2000,
					'receiver' => 1,
				),
			);

			$this->Esevent->setNotification($data[0]);
			$lastInsertedId = $this->Esevent->getInsertID();

			$result = $this->Esevent->find('count',
				array(
					'conditions' => array(
						'subject' => 1000,
						'event' => 1,
					)
				));
			$this->assertEqual($result, 1);

			$result = $this->Esevent->Esnotification->find('count',
				array(
					'conditions' => array(
						'esevent_id' => $this->Esevent->getInsertID(),
						'esreceiver_id' => 1,
					)
				));
			$this->assertEqual($result, 1);

			/*
			 * same event but different user
			 *
			 * reuses the existing event but should make a new notification
			 */
			$this->Esevent->setNotification($data[1]);

			$result = $this->Esevent->find('count',
				array(
					'conditions' => array(
						'subject' => 1000,
						'event' => 1,
					)
				));
			$this->assertEqual($result, 1);

			// no new entry was made in the event table
			$this->assertEqual($lastInsertedId, $this->Esevent->getInsertID());

			// new entry was made in notification table
			$result = $this->Esevent->Esnotification->find('count',
				array(
					'conditions' => array(
						'esevent_id' => $this->Esevent->getInsertID(),
						'esreceiver_id' => 1,
					)
				));
			$this->assertEqual($result, 2);
		}

		/**
		 * testDeleteNotification method
		 *
		 * @return void
		 */
		public function testDeleteNotificationExisting() {
			$data = array(
				'user_id' => 3,
				'subject' => 1,
				'event' => 1,
				'receiver' => 1,
			);

			$this->Esevent->Esnotification = $this->getMock('Esnotification',
				array('delete'),
				array(false, 'esnotifications', 'test')
			);
			$this->Esevent->Esnotification->expects($this->once())
					->method('delete')
					->with(3);

			$this->Esevent->deleteNotification($data);
		}

		public function testDeleteNotificationNonExisting() {
			$data = array(
				'user_id' => 3,
				'subject' => 9999,
				'event' => 1,
				'receiver' => 1,
			);

			$this->Esevent->Esnotification = $this->getMock('Esnotification',
				array('delete'),
				array(false, 'esnotifications', 'test')
			);
			$this->Esevent->Esnotification->expects($this->never())
					->method('delete');

			$this->Esevent->deleteNotification($data);
		}

		public function testCheckEventsForUser() {
			$notfications = array(
				array(
					'subject' => 1,
					'event' => 'Model.Entry.replyToEntry',
					'receiver' => 'EmailNotification',
				),
				array(
					'subject' => 1,
					'event' => 'Model.Entry.replyToThread',
					'receiver' => 'EmailNotification',
				),
				array(
					'subject' => 2,
					'event' => 'Model.Entry.replyToThread',
					'receiver' => 'EmailNotification',
				),
			);

			$result = $this->Esevent->checkEventsForUser(1, $notfications);
			$expected = array(true, false, false);
			$this->assertEqual($result, $expected);
		}

		public function testDeleteSubject() {
			$allNotificationsBefore = $this->Esevent->find('all');
			$allNotificationsBefore = Hash::extract($allNotificationsBefore,
				'{n}.Esnotification.{n}');
			$notificationsBefore = $this->Esevent->find('all',
				array(
					'conditions' => array(
						'event' => array(1, 3)
					)
				)
			);
			$notificationsBefore = Hash::extract($notificationsBefore,
				'{n}.Esnotification.{n}');
			$expectedNotifications = array_merge(Hash::diff($allNotificationsBefore,
				$notificationsBefore));

			$allEventsBefore = $this->Esevent->find('all');
			$eventsBefore = $this->Esevent->find('all',
				array(
					'conditions' => array('event' => array(1, 3))
				));
			// array_merge to reset array keys
			$expectedEvents = array_merge(Hash::diff($allEventsBefore,
				$eventsBefore));

			$this->Esevent->deleteSubject(1, 'entry');

			// Check that events are deleted
			$result = $this->Esevent->find('all');
			$this->assertEqual($result, $expectedEvents);

			// Check that notifications are deleted
			$result = $this->Esevent->find('all');
			$result = Hash::extract($result, '{n}.Esnotification.{n}');
			$this->assertEqual($result, $expectedNotifications);
		}

		public function testGetEventSet() {
			$expected = array(
				'Esevent' => array(
					'id' => '1',
					'subject' => '1',
					'event' => '1',
				),
				'Esnotification' => array(
					array(
						'id' => '1',
						'user_id' => '1',
						'esevent_id' => '1',
						'esreceiver_id' => '1',
						'deactivate' => 1234,
					),
				)
			);

			$data = array(
				'user_id' => 1,
				'subject' => 1,
				'event' => 1,
				'receiver' => 1,
			);
			$result = $this->Esevent->getEventSet($data);
			$this->assertEqual($result, $expected);
		}

		/**
		 * testGetUsersForEventOnSubject method
		 *
		 * @return void
		 */
		public function testGetUsersForEventOnSubject() {
			$expected = array(
				(int)0 => array(
					'id' => '1',
					'username' => 'Alice',
					'user_email' => 'alice@example.com',
					'Esnotification' => array(
						'id' => '1',
						'deactivate' => '1234',
						'user_id' => '1',
						'esevent_id' => '1'
					)
				),
				(int)1 => array(
					'id' => '3',
					'username' => 'Ulysses',
					'user_email' => 'ulysses@example.com',
					'Esnotification' => array(
						'id' => '3',
						'deactivate' => '3234',
						'user_id' => '3',
						'esevent_id' => '1'
					)
				)
			);
			$result = $this->Esevent->getUsersForEventOnSubjectWithReceiver('Model.Entry.replyToEntry',
				1,
				'EmailNotification');
			$this->assertEqual($result, $expected);
		}

	}

