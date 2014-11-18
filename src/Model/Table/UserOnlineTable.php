<?php

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

    public function initialize(array $config)
    {
        $this->table('useronline');
        $this->addBehavior('Timestamp');
        $this->belongsTo(
            'Users',
            [
                'foreignKey' => 'user_id'
            ]
        );
    }

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
     * @param boolean $loggedIn user is logged-in
     * @throws \InvalidArgumentException
     */
    public function setOnline($id, $loggedIn)
    {
        if (empty($id)) {
            throw new \InvalidArgumentException(
                'Invalid Argument $id in setOnline()'
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
     * @param $id
     * @return bool
     */
    public function setOffline($id)
    {
        $id = $this->_getShortendedId($id);

        return $this->deleteAll(['UserOnline.uuid' => $id], false);
    }

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
     */
    protected function _deleteOutdated($timeDiff = null)
    {
        if ($timeDiff === null) {
            $timeDiff = $this->timeUntilOffline;
        }
        $this->deleteAll(['time <' => time() - ($timeDiff)], false);
    }

    protected function _getShortendedId($id)
    {
        return substr($id, 0, 32);
    }
}
