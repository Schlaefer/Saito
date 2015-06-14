<?php

namespace Saito\Test\App;

use Cake\ORM\TableRegistry;
use Saito\App\Stats;
use Saito\Test\SaitoTestCase;

class StatsTest extends SaitoTestCase
{

    public $fixtures = [
        'app.category',
        'app.entry',
        'app.useronline',
        'app.user'
    ];

    /**
     * Test display method
     *
     * @return void
     */
    public function testAppStats()
    {
        $UserOnline = TableRegistry::get('UserOnline');

        $UserOnline->setOnline(1, false);
        $UserOnline->setOnline(2, true);

        $Stats = new Stats();

        $this->assertEquals(2, $Stats->getNumberOfUsersOnline());
        $this->assertEquals(10, $Stats->getNumberOfRegisteredUsers());
        $this->assertEquals(12, $Stats->getNumberOfPostings());
        $this->assertEquals(5, $Stats->getNumberOfThreads());
        $this->assertEquals(1, $Stats->getNumberOfRegisteredUsersOnline());
        $this->assertEquals(1, $Stats->getNumberOfAnonUsersOnline());
    }
}
