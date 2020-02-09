<?php
declare(strict_types=1);

namespace App\Test\TestCase\Entity;

use Cake\ORM\TableRegistry;
use Saito\Test\SaitoTestCase;

class UserTest extends SaitoTestCase
{
    public $fixtures = ['app.Category', 'app.User'];

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
