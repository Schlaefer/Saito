<?php

namespace App\Model\Table;

use App\Lib\Model\Table\AppTable;
use Cake\ORM\Query;
use Cake\Validation\Validator;

class UserIgnoresTable extends AppTable
{

    /**
     * @var int 3 months
     */
    public $duration = 8035200;

    public function initialize(array $config)
    {
        $this->addBehavior('Cron.Cron', [
            'removeOld' => [
                'id' => 'UserIgnore.removeOld',
                'due' => 'daily',
            ]
        ]);
        $this->belongsTo('Users', [
            // @todo 3.0
            'counterCache' => true,
            'foreignKey' => 'blocked_user_id'
        ]);
    }

    public function ignore($userId, $blockedUserId)
    {
        $exists = $this->_get($userId, $blockedUserId);
        if ($exists) {
            return;
        }
        $data = [
            'user_id' => $userId,
            'blocked_user_id' => $blockedUserId,
            'timestamp' => bDate()
        ];
        $entity = $this->newEntity($data);
        $this->save($entity);

        $this->_dispatchEvent('Event.Saito.User.afterIgnore', [
            'blockedUserId' => $blockedUserId,
            'userId' => $userId
        ]);
    }

    public function unignore($userId, $blockedId)
    {
        $entry = $this->_get($userId, $blockedId);
        if (empty($entry)) {
            return;
        }
        $this->delete($entry['Ignore']['id']);
    }

    protected function _get($userId, $blockedId)
    {
        return $this->find('all', [
            'conditions' => [
                'user_id' => $userId,
                'blocked_user_id' => $blockedId
            ]
        ])->first();
    }

    /**
     * finder: return all users ignored by $userId
     */
    public function findAllIgnoredBy(Query $query, array $options)
    {
        $query
            ->contain([
                'Users' => function (Query $query) {
                    $query->select(['Users.id', 'Users.username']);

                    return $query;
                }
            ])
            ->where(['user_id' => $options['userId']])
            ->order(['Users.username' => 'ASC']);

        return $query;
    }

    public function deleteUser($userId)
    {
        $this->deleteAll(['user_id' => $userId]);
        $this->deleteAll(['blocked_user_id' => $userId]);

        return true;
    }

    /**
     * counts how many users ignore the user with ID $id
     *
     * @param $id
     * @return array
     */
    public function countIgnored($id)
    {
        return count($this->getIgnored($id));
    }

    public function getIgnored($id)
    {
        return $this->find('all', [
                'conditions' => ['blocked_user_id' => $id]
            ]
        )->toArray();
    }

    public function removeOld()
    {
        $q = $this->deleteAll([
            'timestamp <' => bDate(time() - $this->duration)
        ]);
    }

}
