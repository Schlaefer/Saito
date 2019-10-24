<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller\Component;

use App\Controller\AppController;
use App\Model\Table\EntriesTable;
use Cake\Controller\Component;
use Cake\Controller\Component\PaginatorComponent;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Saito\Posting\Posting;
use Saito\User\CurrentUser\CurrentUserInterface;
use Stopwatch\Lib\Stopwatch;

/**
 * Class ThreadsComponent
 *
 * @property PaginatorComponent $Paginator
 * @property AuthUserComponent $AuthUser
 */
class ThreadsComponent extends Component
{

    public $components = ['AuthUser', 'Paginator'];

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
        /** @var EntriesTable */
        $EntriesTable = TableRegistry::getTableLocator()->get('Entries');
        $this->Entries = $EntriesTable;

        /** @var AppController */
        $controller = $this->getController();
        $CurrentUser = $controller->CurrentUser;
        $initials = $this->_getInitialThreads($CurrentUser, $order);
        $threads = $this->Entries->treesForThreads($initials, $order);

        return $threads;
    }

    /**
     * Gets thread ids for paginated entries/index.
     *
     * @param CurrentUserInterface $User current-user
     * @param array $order sort order
     * @return array thread ids
     */
    protected function _getInitialThreads(CurrentUserInterface $User, $order)
    {
        Stopwatch::start('Entries->_getInitialThreads() Paginate');
        $categories = $User->getCategories()->getCurrent('read');
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
        if ($this->AuthUser->isBot()) {
            return;
        }

        /** @var EntriesTable */
        $Entries = TableRegistry::getTableLocator()->get('Entries');
        /** @var AppController */
        $controller = $this->getController();
        $CurrentUser = $controller->CurrentUser;

        if ($type === 'thread') {
            $where = ['tid' => $posting->get('tid')];
            if ($CurrentUser->isLoggedIn()) {
                $where['user_id !='] = $CurrentUser->getId();
            }
            $Entries->increment($where, 'views');

            return;
        }

        if ($CurrentUser->isLoggedIn()
            && ($posting->get('user_id') === $CurrentUser->getId())) {
            return;
        }

        $Entries->increment($posting->get('id'), 'views');
    }
}
