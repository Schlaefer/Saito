<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace SaitoSearch\Controller;

use App\Controller\AppController;
use App\Model\Table\EntriesTable;
use Cake\Chronos\Chronos;
use Cake\Database\Driver\Mysql;
use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use SaitoSearch\Lib\SimpleSearchString;
use Saito\Exception\SaitoForbiddenException;

/**
 * @property EntriesTable $Entries
 */
class SearchesController extends AppController
{
    /** @var $helpers CakePHP helpers */
    public $helpers = ['Form', 'Html', 'Posting'];

    /**
     * {@inheritDoc}
     */
    public function initialize()
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
            $this->Prg->setConfig('queryStringWhitelist', ['page'], false);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow('simple');
    }

    /**
     * Simple search
     */
    public function simple()
    {
        $defaults = [
            'searchTerm' => '',
            'order' => 'time'
        ];

        // @todo pgsql
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
                    'categories' => $this->CurrentUser->Categories->getAll('read'),
                    'searchTerm' => $searchString
                ]
            ],
            // only sort paginate for "page"-query-param
            'whitelist' => ['page']
        ];

        $results = $this->Paginator->paginate($this->Entries, $config);
        $this->set('omittedWords', $searchString->getOmittedWords());
        $this->set('minWordLength', $searchString->getMinWordLength());
        $this->set('results', $results);
    }

    /**
     * Advanced Search
     */
    public function advanced()
    {
        $queryData = $this->request->getQueryParams();

        //// Setup time filter data
        $first = $this->Entries->find()
            ->order(['id' => 'ASC'])
            ->first();
        if ($first) {
            $startDate = $first->get('time');
        } else {
            $startDate = Chronos::now();
        }
        $startYear = $startDate->format('Y');

        // calculate current month and year
        $month = $queryData['month']['month'] ?? $startDate->format('n');
        $year = $queryData['year']['year'] ?? $startYear;
        $this->set(compact('month', 'year', 'startYear'));

        //// Category drop-down data
        $categories = $this->CurrentUser->Categories->getAll('read', 'select');
        $this->set('categories', $categories);

        if (empty($queryData['subject']) && empty($queryData['text'])) {
            // just show form;
            return;
        }

        //// setup find
        $query = $this->Entries
            ->find('search', ['search' => $queryData])
            ->contain(['Categories', 'Users'])
            ->order(['Entries.id' => 'DESC']);

        //// Time filter
        $time = Chronos::createFromDate($year, $month, 1);
        if ($time->year !== $startDate->year || $time->month !== $startDate->month) {
            $query->where(['time >=' => $time]);
        }

        //// Category filter
        $categories = array_flip($categories);
        if (!empty($queryData['category_id'])) {
            $category = $queryData['category_id'];
            if (!in_array($category, $categories)) {
                throw new SaitoForbiddenException("Tried to search category $category.");
            }
            $categories = [$category];
        }
        $query->where(['category_id IN' => $categories]);

        $results = $this->paginate($query);
        $this->set(compact('results'));
    }
}
