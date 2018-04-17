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

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $this->addBehavior(
            'Cron.Cron',
            [
                'removeOld' => [
                    'id' => 'UserIgnore.removeOld',
                    'due' => 'daily',
                ]
            ]
        );
        // cache by how many other a user is ignored
        $this->addBehavior('CounterCache', ['Users' => ['ignore_count']]);
        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', ['foreignKey' => 'blocked_user_id']);
    }

    /**
     * {@inheritDoc}
     */
    public function validationDefault(Validator $validator)
    {
        $validator->setProvider(
            'saito',
            'Saito\Validation\SaitoValidationProvider'
        );

        $validator->notEmpty('user_id')
            ->add(
                'user_id',
                [
                    'assoc' => [
                        'rule' => ['validateAssoc', 'Users'],
                        'last' => true,
                        'provider' => 'saito'
                    ]
                ]
            );

        $validator->notEmpty('blocked_user_id')
            ->add(
                'blocked_user_id',
                [
                    'assoc' => [
                        'rule' => ['validateAssoc', 'Users'],
                        'last' => true,
                        'provider' => 'saito'
                    ]
                ]
            );

        $validator->notEmpty('timestamp');

        return $validator;
    }

    /**
     * User $userId starts to block user $blockedUserId.
     *
     * @param int $userId user-ID
     * @param int $blockedUserId user-ID
     * @return void
     */
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

        $this->_dispatchEvent(
            'Event.Saito.User.afterIgnore',
            [
                'blockedUserId' => $blockedUserId,
                'userId' => $userId
            ]
        );
    }

    /**
     * unignore
     *
     * @param int $userId user-ID
     * @param int $blockedId user-ID
     * @return void
     */
    public function unignore($userId, $blockedId)
    {
        $entity = $this->_get($userId, $blockedId);
        if (empty($entity)) {
            return;
        }
        $this->delete($entity);
    }

    /**
     * Get a single record
     *
     * @param int $userId user-ID
     * @param int $blockedId user-ID
     * @return mixed
     */
    protected function _get($userId, $blockedId)
    {
        return $this->find(
            'all',
            [
                'conditions' => [
                    'user_id' => $userId,
                    'blocked_user_id' => $blockedId
                ]
            ]
        )->first();
    }

    /**
     * get all users ignored by $userId
     *
     * @param string $userId user-ID
     * @return mixed
     */
    public function getAllIgnoredBy($userId)
    {
        $results = $this->find()
            ->contain(
                [
                    'Users' => function (Query $query) {
                        $query->select(['Users.id', 'Users.username']);

                        return $query;
                    }
                ]
            )
            ->where(['user_id' => $userId])
            ->order(['Users.username' => 'ASC'])
            ->all();

        return $results->extract('user');
    }

    /**
     * Delete all records affectiong a particular user
     *
     * @param int $userId user-ID
     * @return bool
     */
    public function deleteUser($userId)
    {
        $this->deleteAll(['user_id' => $userId]);
        $this->deleteAll(['blocked_user_id' => $userId]);

        return true;
    }

    /**
     * counts how many users ignore the user with ID $id
     *
     * @param int $id user-ID
     * @return array
     */
    public function countIgnored($id)
    {
        return count($this->getIgnored($id));
    }

    /**
     * get ignored
     *
     * @param int $id user-ID
     * @return array
     */
    public function getIgnored($id)
    {
        return $this->find(
            'all',
            [
                'conditions' => ['blocked_user_id' => $id]
            ]
        )->toArray();
    }

    /**
     * remove old
     *
     * @return void
     */
    public function removeOld()
    {
        $this->deleteAll(
            ['timestamp <' => bDate(time() - $this->duration)]
        );
    }
}
