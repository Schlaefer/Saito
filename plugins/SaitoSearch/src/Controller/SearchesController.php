<?php

declare(strict_types = 1);

namespace SaitoSearch\Controller;

use App\Controller\AppController;
use App\Model\Table\EntriesTable;
use Cake\Database\Driver\Mysql;
use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use SaitoSearch\Lib\SimpleSearchString;

/**
 * @property EntriesTable $Entries
 */
class SearchesController extends AppController
{

    public $components = [
        'Search.Prg' => [
            'commonProcess' => [
                'allowedParams' => ['nstrict'],
                'keepPassed' => true,
                'filterEmpty' => true,
                'paramType' => 'querystring'
            ]
        ]
    ];

    public $helpers = ['Form', 'Html', 'Posting'];

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Entries');
        $this->Entries->addBehavior('SaitoSearch.SaitoSearch');
        $this->loadComponent('Paginator');
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
     *
     * @todo not implemented in Saito 5
     * @return void
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function advanced(): void
    {
        // year for date drop-down
        $first = $this->Entry->find(
            'first',
            ['contain' => false, 'order' => ['Entry.id' => 'ASC']]
        );
        if ($first !== false) {
            $startDate = strtotime($first['Entry']['time']);
        } else {
            $startDate = time();
        }
        $this->set('start_year', date('Y', $startDate));

        // category drop-down
        $categories = $this->CurrentUser->Categories->getAllowed('list');
        $this->set('categories', $categories);

        // calculate current month and year
        if (isset($this->request->query['month'])) {
            $month = $this->request->query['month'];
            $year = $this->request->query['year'];
        } else {
            $month = date('n', $startDate);
            $year = date('Y', $startDate);
        }

        $this->Prg->commonProcess();
        $query = $this->Prg->parsedParams();

        if (!empty($query['subject']) || !empty($query['text']) ||
            !empty($query['name'])
        ) {
            // strict username search: set before parseCriteria
            if (!empty($this->request->query['nstrict'])) {
                // presetVars controller var isn't working in Search v2.3
                $this->Entry->filterArgs['name']['type'] = 'value';
            }

            $settings = [
                    'conditions' => $this->Entry->parseCriteria($query),
                    'order' => ['Entry.time' => 'DESC'],
                    'paramType' => 'querystring'
                ] + $this->_paginateConfig;

            $time = mktime(0, 0, 0, $month, 1, $year);
            if (!$time) {
                throw new BadRequestException;
            }
            $settings['conditions']['time >'] = date(
                'Y-m-d H:i:s',
                mktime(0, 0, 0, $month, 1, $year)
            );

            if (isset($query['category']) && (int)$query['category'] !== 0) {
                if (!isset($categories[(int)$query['category']])) {
                    throw new NotFoundException;
                }
            } else {
                $settings['conditions']['Entry.category'] = $this->CurrentUser
                    ->Categories->getAllowed();
            }
            $this->Paginator->settings = $settings;
            unset(
                $this->request->query['direction'],
                $this->request->query['sort']
            );
            $this->set(
                'results',
                $this->Paginator->paginate(null, null, ['Entry.time'])
            );
        }

        if (!isset($query['category'])) {
            $this->request->data['Entry']['category'] = 0;
        }

        $this->set(compact('month', 'year'));
    }
}
