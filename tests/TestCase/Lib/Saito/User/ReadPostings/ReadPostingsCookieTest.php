<?php

	use Saito\User\ReadPostings;

	App::uses('CurrentUserComponent', 'Controller/Component');

	class ReadPostingsCookieMock extends ReadPostings\ReadPostingsCookie {

		/**
		 * @param mixed $maxPostings
		 */
		public function setMaxPostings($maxPostings) {
			$this->_maxPostings = $maxPostings;
		}

		public function setCookie($Cookie) {
			$this->_Cookie = $Cookie;
		}

		public function setLastRefresh($LR) {
			$this->_LastRefresh = $LR;
		}

		public function __get($name) {
			$property = '_' . $name;
			if (property_exists($this, $property)) {
				return $this->{$property};
			}
		}

		public function __call($name, $arguments) {
			$method = '_' . $name;
			if (is_callable([$this, $method])) {
				return call_user_func_array([$this, $method], $arguments);
			}
		}

	}

	class ReadPostingsCookieTest extends CakeTestCase {

		/**
		 * @var CurrentUserComponent;
		 */
		public $CurrentUser;

		public function testAbstractIsReadNoTimestamp() {
			$this->mock();
			$this->ReadPostings->Cookie->expects($this->once())
				->method('read')
				->will($this->returnValue('1.6'));

			$this->ReadPostings->LastRefresh->expects($this->never())
				->method('isNewerThan');

			$this->assertTrue($this->ReadPostings->isRead(1));
			$this->assertTrue($this->ReadPostings->isRead(6));
			$this->assertFalse($this->ReadPostings->isRead(2));
		}

		public function testAbstractIsReadWithTimestamp() {
			$this->mock();
			$this->ReadPostings->Cookie->expects($this->once())
				->method('read')
				->will($this->returnValue('1'));

			$time = time();

			$this->ReadPostings->LastRefresh->expects($this->at(0))
				->method('isNewerThan')
				->with($time)
				->will($this->returnValue(null));
			$this->ReadPostings->LastRefresh->expects($this->at(1))
				->method('isNewerThan')
				->with($time + 1)
				->will($this->returnValue(null));
			$this->ReadPostings->LastRefresh->expects($this->at(2))
				->method('isNewerThan')
				->with($time + 2)
				->will($this->returnValue(true));
			$this->ReadPostings->LastRefresh->expects($this->at(3))
				->method('isNewerThan')
				->with($time + 3)
				->will($this->returnValue(false));

			$this->assertTrue($this->ReadPostings->isRead(1, $time));
			$this->assertFalse($this->ReadPostings->isRead(2, $time + 1));
			$this->assertTrue($this->ReadPostings->isRead(3, $time + 2));
			$this->assertFalse($this->ReadPostings->isRead(4, $time + 3));
		}

		public function testGet() {
			$this->mock();
			$this->ReadPostings->Cookie->expects($this->once())
				->method('read')
				->will($this->returnValue('1.6'));

			$this->ReadPostings->isRead(1);

			//# test class cache is set
			$expected = [1 => 1, 6 => 1];
			$actual = $this->ReadPostings->readPostings;
			$this->assertEquals($expected, $actual);

			// test caching: should not read cookie a second time
			$this->ReadPostings->isRead(6);
		}

		public function testSet() {
			$this->mock(['_gc', '_get']);
			$this->ReadPostings->expects($this->once())
				->method('_gc');
			$this->ReadPostings->expects($this->once())
				->method('_get')
				->will($this->returnValue([1 => 1, 2 => 1]));

			$time = time();
			$this->ReadPostings->LastRefresh->expects($this->at(0))
				->method('isNewerThan')
				->with($time)
				->will($this->returnValue(false));
			$this->ReadPostings->LastRefresh->expects($this->at(1))
				->method('isNewerThan')
				->with($time + 1)
				->will($this->returnValue(true));
			$this->ReadPostings->LastRefresh->expects($this->at(2))
				->method('isNewerThan')
				->with($time + 2)
				->will($this->returnValue(false));

			/*
			 * 1: already stored, will be stored again but not twice
			 * 2: already stored, will be stored again
			 * 3: not stored, older than last refresh
			 * 4: newly stored
			 */
			$this->ReadPostings->Cookie->expects($this->once())
				->method('write')
				->with('1.2.4');
			$this->ReadPostings->set([
					['Entry' => ['id' => 1, 'time' => $time]],
					['Entry' => ['id' => 3, 'time' => $time + 1]],
					['Entry' => ['id' => 4, 'time' => $time + 2]]
				]
			);

			// test that cookie is unencrypted
			$this->assertFalse($this->ReadPostings->Cookie->encrypt);

			// test that class cache is updated
			$expected = [1 => 1, 2 => 1, 4 => 1];
			$actual = $this->ReadPostings->readPostings;
			$this->assertEquals($expected, $actual);
		}

		public function testSetSingle() {
			$this->mock();

			$this->ReadPostings->LastRefresh->expects($this->at(0))
				->method('isNewerThan')
				->will($this->returnValue(false));
			$this->ReadPostings->Cookie->expects($this->once())
				->method('write')
				->with('4');

			$this->ReadPostings->set(['Entry' => ['id' => 4, 'time' => 0]]);
		}

		public function testGc() {
			$this->mock();
			$this->ReadPostings->Cookie->expects($this->once())
				->method('write')
				->with('5.6');

			$this->ReadPostings->setMaxPostings(2);
			$this->ReadPostings->set([
					['Entry' => ['id' => 1, 'time' => 0]],
					['Entry' => ['id' => 5, 'time' => 1]],
					['Entry' => ['id' => 6, 'time' => 2]]
				]
			);
		}

		public function mock($methods = null) {
			$Collection = new ComponentCollection();
			$this->CurrentUser = new CurrentUserComponent($Collection);
			$this->ReadPostings = $this->getMock(
				'ReadPostingsCookieMock',
				$methods,
				[$this->CurrentUser]
			);

			$this->ReadPostings->setCookie($this->getMock('Object', ['read', 'write', 'delete']));
			$this->ReadPostings->setLastRefresh($this->getMock('Object', ['isNewerThan']));
		}

		public function tearDown() {
			$this->ReadPostings->Cookie->delete();
			unset($this->ReadPostings);
			unset($this->CurrentUser);
			parent::tearDown();
		}

	}
