<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\App;

use App\Model\Entity\User;
use App\Model\Table\UserOnlineTable;
use Cake\Cache\Cache;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Saito\RememberTrait;
use Stopwatch\Lib\Stopwatch;

class Stats
{

    use RememberTrait;

    /**
     * Get registred users online
     *
     * @return Query of UserOnline entities
     */
    public function getRegistredUsersOnline(): Query
    {
        return $this->remember(
            'UsersOnline',
            function () {
                /** @var UserOnlineTable */
                $UserOnline = TableRegistry::get('UserOnline');

                return $UserOnline->getLoggedIn();
            }
        );
    }

    /**
     * Get number of registred users online
     *
     * @return int
     */
    public function getNumberOfRegisteredUsersOnline()
    {
        return $this->remember(
            'numberOfRegisteredUsersOnline',
            function () {
                return $this->getRegistredUsersOnline()->count();
            }
        );
    }

    /**
     * Get number of registered users
     *
     * @return int
     */
    public function getNumberOfRegisteredUsers()
    {
        return $this->_getCached('numberOfRegistredUsers');
    }

    /**
     * Get number of users online
     *
     * @return int
     */
    public function getNumberOfAnonUsersOnline()
    {
        $online = $this->_getCached('numberOfUsersOnline');
        $registred = $this->getNumberOfRegisteredUsersOnline();
        $anon = $online - $registred;
        // compensate for cached online
        $anon = ($anon < 0) ? 0 : $anon;

        return $anon;
    }

    /**
     * Get number of postings
     *
     * @return int
     */
    public function getNumberOfPostings()
    {
        return $this->_getCached('numberOfPostings');
    }

    /**
     * Get number of threads
     *
     * @return int
     */
    public function getNumberOfThreads()
    {
        return $this->_getCached('numberOfThreads');
    }

    /**
     * Get latest registered user
     *
     * @return User entity
     */
    public function getLatestUser()
    {
        return $this->_getCached('latestUser');
    }

    /**
     * Get number of users online
     *
     * @return int
     */
    public function getNumberOfUsersOnline()
    {
        return $this->_getCached('numberOfUsersOnline');
    }

    /**
     * Get properties that can be cached
     *
     * @param string $key key
     * @return mixed
     */
    protected function _getCached($key)
    {
        $stats = $this->remember(
            'init',
            function () {
                Stopwatch::start('Saito\App\Stats::init()');

                $stats = Cache::remember(
                    'header_counter',
                    function () {
                        $Entries = TableRegistry::get('Entries');
                        $Users = TableRegistry::get('Users');
                        $UserOnline = TableRegistry::get('UserOnline');
                        $stats['numberOfPostings'] = $Entries
                            ->find()
                            ->count();
                        $stats['numberOfThreads'] = $Entries->find()
                            ->where(['pid' => 0])
                            ->count();
                        $stats['numberOfRegistredUsers'] = $Users
                            ->find()
                            ->count();
                        $stats['latestUser'] = $Users
                            ->find('latest')
                            ->first();
                        $stats['numberOfUsersOnline'] = $UserOnline
                            ->find()
                            ->count();

                        return $stats;
                    },
                    'short'
                );

                Stopwatch::stop('Saito\App\Stats::init()');

                return $stats;
            }
        );

        return $stats[$key];
    }
}
