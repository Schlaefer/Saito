<?php

namespace App\Test\TestCase\Entity;

use App\Model\Entity\User;
use Cake\ORM\TableRegistry;
use Saito\Test\SaitoTestCase;

class UserTest extends SaitoTestCase
{

    public $fixtures = ['app.category', 'app.user'];

    public function testNumberOfPostings()
    {
        $Users = TableRegistry::get('Users');

        //= zero entries
        $user = $Users->get(4);
        $expected = 0;
        $result = $user->numberOfPostings();
        $this->assertEquals($expected, $result);

        //= multiple entries
        $Users->updateAll(['entry_count' => 101], ['id' => 3]);
        $user = $Users->get(3);
        $expected = 101;
        $result = $user->numberOfPostings();
        $this->assertEquals($expected, $result);
    }
}
