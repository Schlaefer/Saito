<?php

namespace App\Test\TestCase\Model\Table;

use Saito\Test\Model\Table\SaitoTableTestCase;

/**
 * UserIgnore Test Case
 */
class UserIgnoreTest extends SaitoTableTestCase
{

    public $tableClass = 'UserIgnores';

    public $fixtures = ['app.user', 'app.user_ignore'];

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

        $results = $this->Table->find('allIgnoredBy', ['userId' => 3]);
        $this->assertEquals($results->first()->get('user')->get('id'), 1);
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
        $this->assertWithinRange($result->get('timestamp')->toUnixString(), time(), 3);

        $this->Table->ignore(2, 3);
        $results = $this->Table->find('all');
        $this->assertCount(1, $results);

        $this->Table->ignore(3, 4);
        $results = $this->Table->find('all');
        $this->assertCount(2, $results);
    }

    public function testUserIgnoreIgnoredBy()
    {
        $result = $this->Table->find('allIgnoredBy', ['userId' => 3]);
        $this->assertTrue($result->isEmpty());
        $this->Table->ignore(3, 5);
        $this->Table->ignore(3, 1);

        $result = $this->Table->find('allIgnoredBy', ['userId' => 3]);
        $userIds = $result->extract('user.id')->toArray();
        $this->assertEquals($userIds, [1, 5]);
    }

    public function testUserIgnoreRemoveOld()
    {
        $duration = $this->Table->duration;
        $data = [
            [
                'user_id' => 1,
                'blocked_user_id' => 2,
                'timestamp' => bDate(time() - $duration - 1)
            ],
            [
                'user_id' => 1,
                'blocked_user_id' => 3,
                'timestamp' => bDate(time() - $duration)
            ],
        ];
        $entities = $this->Table->newEntities($data);
        foreach ($entities as $entity) {
            $this->Table->save($entity);
        }
        $this->Table->removeOld();
        $results = $this->Table->find('allIgnoredBy', ['userId' => 1]);
        $this->assertCount(1, $results);
        $this->assertEquals($results->first()->get('user')->get('id'), '3');
    }

}
