<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace SaitoSearch\Controller;

use App\Controller\AppController;
use App\Model\Table\EntriesTable;
use Cake\Chronos\Chronos;
use Cake\Database\Driver\Mysql;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\I18n\FrozenDate;
use SaitoSearch\Lib\SimpleSearchString;
use Saito\Exception\SaitoForbiddenException;
use Search\Controller\Component\PrgComponent;

/**
 * @property EntriesTable $Entries
 * @property PrgComponent $Prg
 */
class SearchesController extends AppController
{
    /**
     * {@inheritDoc}
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadModel('Entries');

        $this->loadComponent('Paginator');
        // use setConfig on Component to not merge but overwrite/set the config
        $this->Paginator->setConfig('whitelist', ['page'], false);

        if ($this->getRequest()->getParam('action') === 'simple') {
            $this->Entries->addBehavior('SaitoSearch.SaitoSearch');
        } else {
            $this->Entries->addBehavior('Search.Search');
            $this->loadComponent('Search.Prg');
            $this->Prg->setConfig('actions', ['advanced'], false);
            $this->Prg->setConfig('queryStringWhitelist', [], false);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['simple']);
    }

    /**
     * Simple search
     *
     * @return void|Response
     */
    public function simple()
    {
        $this->set('titleForPage', __d('saito_search', 'simple.t'));

        $defaults = [
            'searchTerm' => '',
            'order' => 'time',
        ];

        // @td pgsql
        $connection = $this->Entries->getConnection();
        if (!($connection->getDriver() instanceof Mysql)) {
            return $this->redirect(['action' => 'advanced']);
        }

        $query = $this->request->getQueryParams();
        $query = array_intersect_key($query, array_flip(['searchTerm', 'order']));
        $query += $defaults;
        $this->set('searchDefaults', $query);

        $showEmptyForm = empty($query['searchTerm']);
        if ($showEmptyForm) {
            return;
        }

        $searchString = new SimpleSearchString($query['searchTerm']);
        $finder = $query['order'] === 'rank' ? 'simpleSearchByRank' : 'simpleSearchByTime';
        $config = [
            'finder' => [
                $finder => [
                    'categories' => $this->CurrentUser->getCategories()->getAll('read'),
                    'searchTerm' => $searchString,
                ],
            ],
            // only sort paginate for "page"-query-param
            'whitelist' => ['page'],
        ];

        $results = $this->Paginator->paginate($this->Entries, $config);
        $this->set('omittedWords', $searchString->getOmittedWords());
        $this->set('minWordLength', $searchString->getMinWordLength());
        $this->set('results', $results);
        $this->set('showBottomNavigation', true);
    }

    /**
     * Advanced Search
     *
     * @return void
     */
    public function advanced()
    {
        $this->set('titleForPage', __d('saito_search', 'advanced.t'));

        $queryData = $this->request->getQueryParams();

        /// Setup time filter data
        $first = $this->Entries->find()
            ->order(['id' => 'ASC'])
            ->first();
        if ($first) {
            $startDate = $first->get('time');
            /// Limit default search range to one year in the past
            $aYearAgo = new FrozenDate('-1 year');
            $defaultDate = $startDate < $aYearAgo ? $aYearAgo : $startDate;
        } else {
            /// No entries yet
            $startDate = $defaultDate = Chronos::now();
        }
        $startYear = $startDate->format('Y');

        // calculate current month and year
        $month = $queryData['month']['month'] ?? $defaultDate->month;
        $year = $queryData['year']['year'] ?? $defaultDate->year;
        $this->set(compact('month', 'year', 'startYear'));

        /// Category drop-down data
        $categories = $this->CurrentUser->getCategories()->getAll('read', 'select');
        $this->set('categories', $categories);

        if (empty($queryData['subject']) && empty($queryData['text']) && empty($queryData['name'])) {
            // just show form;
            return;
        }

        /// setup find
        $query = $this->Entries
            ->find('search', ['search' => $queryData])
            ->contain(['Categories', 'Users'])
            ->order(['Entries.id' => 'DESC']);

        /// Time filter
        $time = Chronos::createFromDate($year, $month, 1);
        if ($time->year !== $defaultDate->year || $time->month !== $defaultDate->month) {
            $query->where(['time >=' => $time]);
        }

        /// Category filter
        $categories = array_flip($categories);
        if (!empty($queryData['category_id'])) {
            $category = $queryData['category_id'];
            if (!in_array($category, $categories)) {
                throw new SaitoForbiddenException(
                    "Tried to search category $category.",
                    ['CurrentUser' => $this->CurrentUser]
                );
            }
            $categories = [$category];
        }
        $query->where(['category_id IN' => $categories]);

        $results = $this->paginate($query);
        $this->set(compact('results'));
        $this->set('showBottomNavigation', true);
    }
}
