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

use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Stopwatch\Lib\Stopwatch;

/**
 * Stores which users are online
 *
 * Storage can be nopersistent as it is constantly rebuild with live-data.
 *
 * Field notes:
 * - `time` - Timestamp as int unix-epoch instead regular DATETIME. Makes it
 *   cheap to clear out-timed users by comparing int values.
 */
class UserOnlineTable extends Table
{
    /**
     * Time in seconds until a user is considered offline
     *
     * @var int
     */
    public $timeUntilOffline = 1200;

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $this->setTable('useronline');

        $this->addBehavior('Timestamp');

        $this->addBehavior(
            'Cron.Cron',
            [
                'gc' => [
                    'id' => 'UserOnline.deleteGone',
                    'due' => '+1 minutes',
                ],
            ]
        );

        $this->belongsTo(
            'Users',
            [
                'foreignKey' => 'user_id'
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            //= uuid
            ->notEmpty('uuid')
            ->requirePresence('uuid')
            ->add(
                'uuid',
                [
                    'isUnique' => [
                        'rule' => 'validateUnique',
                        'provider' => 'table'
                    ],
                ]
            );

        return $validator;
    }

    /**
     * Sets user with `$id` online
     *
     * @param int|string $id user-ID
     * @param bool $loggedIn user is logged-in
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setOnline($id, bool $loggedIn): void
    {
        if (empty($id)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid Argument $id in setOnline(): %s', $id)
            );
        }

        $now = time();
        $id = $this->getShortendedId((string)$id);
        $data = [
            'uuid' => $id,
            'logged_in' => $loggedIn,
            'time' => $now
        ];

        if ($loggedIn) {
            $data['user_id'] = $id;
        }

        $user = $this->find()
            ->where(['uuid' => $id])
            ->first();

        if ($user) {
            // only hit database if timestamp is outdated
            if ($user->get('time') < ($now - $this->timeUntilOffline)) {
                $user->set('time', $now);
                $this->save($user);
            }
        } else {
            $user = $this->newEntity($data);
            $this->save($user);
        }
    }

    /**
     * Removes user with uuid `$id` from UserOnline
     *
     * @param int|string $id id
     * @return void
     */
    public function setOffline($id): void
    {
        $id = $this->getShortendedId((string)$id);
        $this->deleteAll(['UserOnline.uuid' => $id]);
    }

    /**
     * Get all logged-in users
     *
     * Don't use directly but use \Saito\App\Stats
     *
     * @td @sm make finder
     *
     * @return Query
     */
    public function getLoggedIn(): Query
    {
        Stopwatch::start('UserOnline->getLoggedIn()');
        $loggedInUsers = $this->find(
            'all',
            [
                'contain' => [
                    'Users' => [
                        'fields' => ['id', 'user_type', 'username']
                    ]
                ],
                'conditions' => ['UserOnline.logged_in' => true],
                'fields' => ['id'],
                'order' => ['LOWER(Users.username)' => 'ASC']
            ]
        );
        Stopwatch::stop('UserOnline->getLoggedIn()');

        return $loggedInUsers;
    }

    /**
     * Removes users which weren't online $timeDiff seconds
     *
     * @return void
     */
    public function gc(): void
    {
        $this->deleteAll(['time <' => time() - ($this->timeUntilOffline)]);
    }

    /**
     * shorten string
     *
     * @param string $id string
     * @return string
     */
    protected function getShortendedId(string $id)
    {
        return substr($id, 0, 32);
    }
}
