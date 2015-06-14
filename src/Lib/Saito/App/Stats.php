<?php

namespace Saito\App;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Saito\RememberTrait;
use Stopwatch\Lib\Stopwatch;

class Stats
{

    use RememberTrait;

    public function getRegistredUsersOnline()
    {
        return $this->remember(
            'UsersOnline',
            function () {
                $UserOnline = TableRegistry::get('UserOnline');

                return $UserOnline->getLoggedIn();
            }
        );
    }

    public function getNumberOfRegisteredUsersOnline()
    {
        return $this->remember(
            'numberOfRegisteredUsersOnline',
            function () {
                return $this->getRegistredUsersOnline()->count();
            }
        );
    }

    public function getNumberOfRegisteredUsers()
    {
        return $this->get('numberOfRegistredUsers');
    }

    public function getNumberOfAnonUsersOnline()
    {
        $online = $this->get('numberOfUsersOnline');
        $registred = $this->getNumberOfRegisteredUsersOnline();
        $anon = $online - $registred;
        // compensate for cached online
        $anon = ($anon < 0) ? 0 : $anon;

        return $anon;
    }

    public function getNumberOfPostings()
    {
        return $this->get('numberOfPostings');
    }

    public function getNumberOfThreads()
    {
        return $this->get('numberOfThreads');
    }

    public function getLatestUser()
    {
        return $this->get('latestUser');
    }

    public function getNumberOfUsersOnline() {
        return $this->get('numberOfUsersOnline');
    }

    protected function get($key)
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
