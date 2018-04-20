<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UserIgnoresTable;
use Saito\Test\Model\Table\SaitoTableTestCase;

/**
 * UserIgnore Test Case
 */
class UserIgnoreTest extends SaitoTableTestCase
{

    /**
     * @var UserIgnoresTable
     */
    public $Table;

    public $tableClass = 'UserIgnores';

    public $fixtures = ['app.category', 'app.user', 'app.user_ignore'];

    public function testUserIgnoreCountIngored()
    {
        $this->Table->ignore(1, 3);
        $this->Table->ignore(2, 3);

        $this->assertEquals(2, $this->Table->countIgnored(3));
    }

    public function testUserIgnoreDeleteUser()
    {
        $this->Table->ignore(1, 2);
        $this->Table->ignore(2, 3);
        $this->Table->ignore(3, 1);

        $this->Table->deleteUser(2);

        $results = $this->Table->getAllIgnoredBy(3);
        $this->assertEquals($results->first()->get('id'), 1);
    }

    public function testUserIgnoreIgnore()
    {
        $this->Table->ignore(2, 3);

        $results = $this->Table->find('all');
        $this->assertCount(1, $results);

        $result = $results->first();
        $this->assertEquals($result->get('id'), '1');
        $this->assertEquals($result->get('user_id'), '2');
        $this->assertEquals($result->get('blocked_user_id'), '3');
        $this->assertWithinRange(
            $result->get('timestamp')->toUnixString(),
            time(),
            3
        );

        $this->Table->ignore(2, 3);
        $results = $this->Table->find('all');
        $this->assertCount(1, $results);

        $this->Table->ignore(3, 4);
        $results = $this->Table->find('all');
        $this->assertCount(2, $results);
    }

    public function testUserIgnoreIgnoredBy()
    {
        $result = $this->Table->getAllIgnoredBy(3);
        $this->assertTrue($result->isEmpty());
        $this->Table->ignore(3, 5);
        $this->Table->ignore(3, 1);

        $result = $this->Table->getAllIgnoredBy(3);
        $userIds = $result->extract('id')->toArray();
        $this->assertEquals($userIds, [1, 5]);
    }

    public function testUserIgnoreRemoveOld()
    {
        $duration = UserIgnoresTable::DURATION;
        $data = [
            [
                'user_id' => 1,
                'blocked_user_id' => 2,
                'timestamp' => bDate(time() - $duration - 1)
            ],
            [
                'user_id' => 1,
                'blocked_user_id' => 3,
                'timestamp' => bDate(time() - $duration + 1)
            ],
        ];
        $entities = $this->Table->newEntities($data);
        foreach ($entities as $entity) {
            $this->Table->save($entity);
        }
        $this->Table->removeOld();
        $result = $this->Table->getAllIgnoredBy(1);
        $this->assertEquals(1, count($result->toArray()));
        $this->assertEquals($result->first()->get('id'), '3');
    }

    public function testCounterCache()
    {
        $user = $this->Table->Users->find()->where(['id' => 3])->first();
        $this->assertEquals(0, $user->get('ignore_count'));

        $this->Table->ignore(2, 3);

        $user = $this->Table->Users->find()->where(['id' => 3])->first();
        $this->assertEquals(1, $user->get('ignore_count'));
    }
}
