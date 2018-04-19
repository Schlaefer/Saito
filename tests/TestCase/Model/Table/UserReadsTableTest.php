<?php

namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Saito\Test\Model\Table\SaitoTableTestCase;

class UserReadsTableTest extends SaitoTableTestCase
{

    public $tableClass = 'UserReads';

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.user_read',
        'app.user',
        'app.user_online',
        'app.entry',
        'app.category',
        'plugin.bookmarks.bookmark'
    ];

    /**
     * tests that only new entries are stored to the DB
     */
    public function testSetEntriesForUserExistingEntry()
    {
        $userId = 1;
        $entryId = 2;

        $User = $this->getMockForModel('UserReads', ['create', 'getUser']);

        $User->expects($this->once())
            ->method('getUser')
            ->with($userId)
            ->will($this->returnValue([$entryId]));
        $User->expects($this->never())->method('create');

        $User->setEntriesForUser([$entryId], $userId);
    }
}
