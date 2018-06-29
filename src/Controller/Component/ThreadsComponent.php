<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller\Component;

use App\Model\Table\EntriesTable;
use Cake\Controller\Component;
use Cake\Controller\Component\PaginatorComponent;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Saito\App\Registry;
use Saito\Posting\Posting;
use Stopwatch\Lib\Stopwatch;

/**
 * Class ThreadsComponent
 *
 * @property PaginatorComponent $Paginator
 * @package App\Controller\Component
 */
class ThreadsComponent extends Component
{

    public $components = ['Paginator'];

    /**
     * Entries table
     *
     * @var EntriesTable
     */
    private $Entries;

    /**
     * Load paginated threads
     *
     * @param mixed $order order to apply
     * @return array
     */
    public function paginate($order)
    {
        $this->Entries = TableRegistry::get('Entries');
        $CurrentUser = $this->_getCurrentUser();
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
        if (empty($categories)) {
            // no readable categories for user (e.g. no public categories
            return [];
        }

        ////! Check DB performance after changing conditions/sorting!
        $customFinderOptions = [
            'conditions' => [
                'Entries.category_id IN' => $categories
            ],
            // @td sanitize input?
            'limit' => Configure::read('Saito.Settings.topics_per_page'),
            'order' => $order,
            // Performance: Custom counter from categories counter-cache;
            // avoids a costly COUNT(*) DB call counting all pages for pagination.
            'counter' => function ($query) use ($categories) {
                $results = $this->Entries->Categories->find('all')
                ->select(['thread_count'])
                ->where(['id IN' => $categories])
                ->all();
                $count = array_reduce(
                    $results->toArray(),
                    function ($carry, Entity $entity) {
                        return $carry + $entity->get('thread_count');
                    },
                    0
                );

                return $count;
            }
        ];

        $settings = [
            'finder' => ['indexPaginator' => $customFinderOptions],
        ];

        // use setConfig on Component to not merge but overwrite/set the config
        $this->Paginator->setConfig('whitelist', ['page'], false);
        $initialThreads = $this->Paginator->paginate($this->Entries, $settings);

        $initialThreadsNew = [];
        foreach ($initialThreads as $k => $v) {
            $initialThreadsNew[$k] = $v['id'];
        }
        Stopwatch::stop('Entries->_getInitialThreads() Paginate');

        return $initialThreadsNew;
    }

    /**
     * Increment views for posting if posting doesn't belong to current user.
     *
     * @param Posting $posting posting
     * @param string $type type
     * - 'null' increment single posting
     * - 'thread' increment all postings in thread
     *
     * @return void
     */
    public function incrementViews(Posting $posting, $type = null)
    {
        $CurrentUser = $this->_getCurrentUser();
        if ($CurrentUser->isBot()) {
            return;
        }

        // @bogus why not use $this->Entries?
        /** @var $Entries EntriesTable */
        $Entries = TableRegistry::get('Entries');
        $cUserId = $CurrentUser->getId();

        if ($type === 'thread') {
            $where = ['tid' => $posting->get('tid')];
            if ($cUserId) {
                $where['user_id !='] = $cUserId;
            }
            $Entries->increment($where, 'views');
        } elseif ($posting->get('user_id') !== $cUserId) {
            $Entries->increment($posting->get('id'), 'views');
        }
    }

    /**
     * Get CurrentUser
     *
     * @return CurrentUserComponent
     */
    protected function _getCurrentUser()
    {
        return Registry::get('CU');
    }
}
