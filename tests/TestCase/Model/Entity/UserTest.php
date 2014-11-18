<?php

	namespace App\Test\TestCase\Entity;

	use App\Model\Entity\User;
	use Cake\ORM\TableRegistry;
	use Cake\TestSuite\TestCase;

	class UserTest extends TestCase {

		public $fixtures = ['app.entry', 'app.user'];

		public function testNumberOfPostings() {
			$Users = TableRegistry::get('Users');

			//= zero entries
			$user = $Users->get(4);
			$expected = 0;
			$result = $user->numberOfPostings();
			$this->assertEquals($expected, $result);

			//= multiple entries
			$user = $Users->get(3);
			$expected = 7;
			$result = $user->numberOfPostings();
			$this->assertEquals($expected, $result);
		}

	}
