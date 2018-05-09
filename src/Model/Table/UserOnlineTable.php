<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Model\Table;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Stopwatch\Lib\Stopwatch;

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
     * @param string $id identifier
     * @param bool $loggedIn user is logged-in
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setOnline($id, $loggedIn)
    {
        if (empty($id)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid Argument $id in setOnline(): %s', $id)
            );
        }
        if (!is_bool($loggedIn)) {
            throw new \InvalidArgumentException(
                'Invalid Argument $logged_in in setOnline()'
            );
        }

        $now = time();
        $id = $this->_getShortendedId($id);
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

        $this->_deleteOutdated();
    }

    /**
     * Removes user with uuid `$id` from UserOnline
     *
     * @param string $id id
     * @return bool
     */
    public function setOffline($id)
    {
        $id = $this->_getShortendedId($id);

        return $this->deleteAll(['UserOnline.uuid' => $id]);
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
    public function getLoggedIn()
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
     * deletes gone user
     *
     * Gone users are user who are not seen for $time_diff minutes.
     *
     * @param string $timeDiff in minutes
     * @return void
     */
    protected function _deleteOutdated($timeDiff = null)
    {
        if ($timeDiff === null) {
            $timeDiff = $this->timeUntilOffline;
        }
        $this->deleteAll(['time <' => time() - ($timeDiff)]);
    }

    /**
     * shorten string
     *
     * @param string $id string
     * @return string
     */
    protected function _getShortendedId($id)
    {
        return substr($id, 0, 32);
    }
}
