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

use Cake\Log\LogTrait;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Psr\Log\LogLevel;
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
    use LogTrait;

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
     * @param string $id usually user-ID (logged-in user) or session_id (not logged-in)
     * @param bool $loggedIn user is logged-in
     * @return void
     */
    public function setOnline(string $id, bool $loggedIn): void
    {
        $now = time();
        $id = $this->getShortendedId((string)$id);

        $user = $this->find()->where(['uuid' => $id])->first();

        if ($user) {
            // [Performance] Only hit database if timestamp is about to get outdated.
            //
            // Adjust to sane values taking JS-frontend status ping time
            // intervall into account.
            if ($user->get('time') < ($now - (int)($this->timeUntilOffline * 80 / 100))) {
                $user->set('time', $now);
                $this->save($user);
            }

            return;
        }

        $data = ['logged_in' => $loggedIn, 'time' => $now, 'uuid' => $id];
        if ($loggedIn) {
            $data['user_id'] = (int)$id;
        }
        $user = $this->newEntity($data);

        try {
            $this->save($user);
        } catch (\PDOException $e) {
            // We saw that some mobile browsers occasionaly send two requests at
            // the same time. On of the two requests was always the status-ping.
            // Working theory: cause is a power-coalesced status-ping now
            // bundled with a page reload on tab-"resume" esp. with http/2.
            //
            // When the second request arrives (ns later) the first request
            // hasn't persistet its save to the DB yet (which happens in the ms
            // range). So the second request doesn't see the user online and
            // tries to save the same "uuid" again. The DB will not have any of
            // that nonsense after it set the "uuid" from the first request on a
            // unique column, and raises an error which may be experienced by
            // the user.
            //
            // Since the first request did the necessary work of marking the
            // user online, we suppress this error, assuming it will only happen
            // in this particular situation. *knocks on wood*
            if ($e->getCode() == 23000 && strstr($e->getMessage(), 'uuid')) {
                $this->log(
                    'Cought duplicate "uuid" key exception in UserOnline::setOnline.',
                    LogLevel::INFO,
                    'saito.info'
                );
            }
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
     * Shortens a string to fit in the uuid table-field
     *
     * @param string $id string
     * @return string
     */
    protected function getShortendedId(string $id)
    {
        return substr($id, 0, 32);
    }
}
