<?php

	App::uses('Entry', 'Model');

	class EntryTest extends CakeTestCase {

		public $fixtures = array( 'app.user', 'app.user_online', 'app.entry', 'app.category', 'app.smiley', 'app.smiley_code', 'app.setting', 'app.upload' );

		public function testBeforeValidate() {

			//* save entry with text
			$entry['Entry'] = array(
					'user_id' => 3,
					'subject' => 'Test Subject',
					'Text' => 'Text Text',
					'pid' => '2',
			);
		}

		public function testToggle() {

			$this->Entry->id = 2;

			//* test that thread is unlocked
			$result = $this->Entry->field('locked');
			$this->assertTrue($result == FALSE);

			//* lock thread
			$this->Entry->toggle('locked');
			$result = $this->Entry->field('locked');
			$this->assertTrue($result == TRUE);

			//* unlock thread again
			$this->Entry->toggle('locked');
			$result = $this->Entry->field('locked');
			$this->assertTrue($result == FALSE);
		}

		public function testDeleteTree() {

			//* test thread exists before we delete it
			$result = $this->Entry->find('count',
					array( 'conditions' => array( 'tid' => '1' ) ));
			$expected = 3;
			$this->assertEqual($result, $expected);

			//* try to delete subentry
			$this->Entry->id = 2;
			$result = $this->Entry->deleteTree();
			$this->assertFalse($result);

			$result = $this->Entry->find('count',
					array( 'conditions' => array( 'tid' => '1' ) ));
			$expected = 3;
			$this->assertEqual($result, $expected);

			//* try to delete thread
			$this->Entry->id = 1;
			$result = $this->Entry->deleteTree();
			$this->assertTrue($result);

			$result = $this->Entry->find('count',
					array( 'conditions' => array( 'tid' => '1' ) ));
			$expected = 0;
			$this->assertEqual($result, $expected);
		}

    public function testAnonymizeEntriesFromUser() {
      $this->Entry->anonymizeEntriesFromUser(3);

      // user has no entries anymore
      $expected = 0;
      $result = $this->Entry->find('count', array(
          'conditions' => array ('Entry.user_id' => 3)
      ));
      $this->assertEqual($result, $expected);

      // entries are now assigned to user_id 0
      $expected = 3;
      $result = $this->Entry->find('count', array(
          'conditions' => array ('Entry.user_id' => 0)
      ));
      $this->assertEqual($result, $expected);

      // name is removed
      $expected = 0;
      $result = $this->Entry->find('count', array(
          'conditions' => array ('Entry.name' => 'Ulysses')
      ));
      $this->assertEqual($result, $expected);

      // edited by is removed
      $expected = 0;
      $result = $this->Entry->find('count', array(
          'conditions' => array ('Entry.edited_by' => 'Ulysses')
      ));
      $this->assertEqual($result, $expected);

      // ip is removed
      $expected = 0;
      $result = $this->Entry->find('count', array(
          'conditions' => array ('Entry.ip' => '1.1.1.1')
      ));
      $this->assertEqual($result, $expected);

    }

		public function startTest() {
			$this->Entry = ClassRegistry::init('Entry');
		}

	}

?>