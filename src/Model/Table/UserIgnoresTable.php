<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Model\Table;

use App\Lib\Model\Table\AppTable;
use Cake\ORM\Query;
use Cake\Validation\Validator;
use Saito\Validation\SaitoValidationProvider;

class UserIgnoresTable extends AppTable
{
    /**
     * @var int 3 months
     */
    public const DURATION = 8035200;

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
                    'due' => '+1 day',
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
        $validator->setProvider('saito', SaitoValidationProvider::class);

        // @td move to application rule
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

        // @td move to application rule
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
    public function ignore(int $userId, int $blockedUserId): void
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

        $this->dispatchDbEvent(
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
    public function unignore(int $userId, int $blockedId): void
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
    protected function _get(int $userId, int $blockedId)
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
     * @param int $userId user-ID
     * @return mixed
     */
    public function getAllIgnoredBy(int $userId)
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
     * @return void
     */
    public function deleteUser(int $userId): void
    {
        $this->deleteAll(['user_id' => $userId]);
        $this->deleteAll(['blocked_user_id' => $userId]);
    }

    /**
     * counts how many users ignore the user with ID $id
     *
     * @param int $id user-ID
     * @return int
     */
    public function countIgnored(int $id): int
    {
        return count($this->getIgnored($id));
    }

    /**
     * get ignored
     *
     * @param int $id user-ID
     * @return array
     */
    public function getIgnored(int $id): array
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
    public function removeOld(): void
    {
        $this->deleteAll(
            ['timestamp <' => bDate(time() - self::DURATION)]
        );
    }
}
