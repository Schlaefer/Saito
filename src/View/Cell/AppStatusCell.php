<?php

namespace App\View\Cell;

use App\Lib\View\Cell\AppStatisticTrait;
use Cake\Cache\Cache;
use Cake\View\Cell;
use Saito\App\Registry;
use Stopwatch\Lib\Stopwatch;

/**
 * AppStatus cell
 */
class AppStatusCell extends Cell
{

    use AppStatisticTrait;

    /**
     * List of valid options that can be passed into this
     * cell's constructor.
     *
     * @var array
     */
    protected $_validCellOptions = [];

    /**
     * Default display method.
     *
     * @return void
     */
    public function display()
    {
        $this->_setData();
    }

    protected function _setData()
    {
        Stopwatch::start('AppStatusCell::display()');

        $this->set('CurrentUser', Registry::get('CU'));
        $this->set('UsersOnline', $this->getUserOnline());

        $headCounter = Cache::remember(
            'header_counter',
            function () {
                $this->loadModel('Users');
                $this->loadModel('Entries');

                $stats['entries'] = $this->Entries->find('all')->count();
                $stats['threads'] = $this->Entries->find('all')
                    ->where(['pid' => 0])
                    ->count();
                $stats['user'] = $this->Users->find()->count();
                $stats['user_online'] = $this->UserOnline->find()->count();

                $stats['latestUser'] = $this->Users->find('latest')->first();

                return $stats;
            },
            'short'
        );

        $headCounter['user_registered'] = $this->getNUserOnline();
        $anonUser = $headCounter['user_online'] - $headCounter['user_registered'];
        // compensate for cached 'user_online' so that user_anonymous can't get negative
        $headCounter['user_anonymous'] = ($anonUser < 0) ? 0 : $anonUser;

        $this->set('HeaderCounter', $headCounter);
        Stopwatch::stop('AppStatusCell::display()');
    }

}
