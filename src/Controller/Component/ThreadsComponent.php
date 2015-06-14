<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Stopwatch\Lib\Stopwatch;

class ThreadsComponent extends Component
{

    public $components = ['Paginator'];

    /**
     * Load paginated threads
     *
     * @param mixed $order order to apply
     * @return array
     */
    public function paginate($order)
    {
        $this->Entries = TableRegistry::get('Entries');
        $CurrentUser = $this->_registry->getController()->CurrentUser;

        $initials = $this->_getInitialThreads($CurrentUser, $order);
        $threads = $this->Entries->treesForThreads($initials, $order);
        return $threads;
    }

    /**
     * Gets thread ids for paginated entries/index.
     *
     * @param CurrentUserComponent $User current-user
     * @param array $order sort order
     * @return array thread ids
     */
    protected function _getInitialThreads(CurrentUserComponent $User, $order)
    {
        Stopwatch::start('Entries->_getInitialThreads() Paginate');

        $categories = $User->Categories->getCurrent('read');

        //! Check DB performance after changing conditions/sorting!
        $customFinderOptions = [
            'conditions' => [
                'Entries.category_id IN' => $categories
            ],
            'limit' => Configure::read('Saito.Settings.topics_per_page'),
            'order' => $order
        ];
        $settings = [
            'finder' => ['indexPaginator' => $customFinderOptions],
        ];

        /* disallow sorting or ordering via request */
        //$this->loadComponent('Paginator');
        // this is the only way to set the whitelist
        // loadComponent() or paginate() do not work
        $this->Paginator->config('whitelist', ['page'], false);
        $initialThreads = $this->Paginator->paginate($this->Entries, $settings);

        $initialThreadsNew = [];
        foreach ($initialThreads as $k => $v) {
            $initialThreadsNew[$k] = $v['id'];
        }
        Stopwatch::stop('Entries->_getInitialThreads() Paginate');

        return $initialThreadsNew;
    }
}
