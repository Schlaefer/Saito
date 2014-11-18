<?php

    namespace App\Controller\Admin;

    use App\Controller\AppController;

class AdminsController extends AppController
{

    public $helpers = ['Admin', 'Flot', 'Sitemap.Sitemap'];

    public function index()
    {
    }

    /**
         * Empty out all caches
         */
    public function emptyCaches()
    {
        $this->CacheSupport->clear();
        $this->Flash->set(__('Caches cleared.'), ['element' =>'success']);
        return $this->redirect($this->referer());
    }


    // @todo 3.0
    public function admin_logs()
    {
        // order here is output order in frontend
        $_logsToRead = ['error', 'debug'];

        $_logsToRead = glob(LOGS . '*.log');
        if (!$_logsToRead) {
            return;
        }

        // will contain ['error' => '<string>', 'debug' => '<string>']
        $_logs = [];
        foreach ($_logsToRead as $_path) {
            $_content = '';
            $_size = filesize($_path);
            $_content = file_get_contents($_path, false, null, $_size - 65536);
            $name = basename($_path);
            $_logs[$name] = $_content;
        }
        $this->set('logs', $_logs);
    }

    public function plugins()
    {
    }

    public function stats()
    {
        $postingsPA = $this->getYearStats('Entry', 'time');
        $registrationsPA = $this->getYearStats('User', 'registered');
        $activeUserPA = $this->getUserWithPostingsPerYear();
        $averagePostingsPerUserPA = $this->getAveragePPU(
            $postingsPA,
            $activeUserPA
        );
        $this->set(
            compact(
                'averagePostingsPerUserPA',
                'postingsPA', 'activeUserPA', 'registrationsPA'
            )
        );
        $this->set('categoryPostingsPA', $this->getCategoriesPerYear());
    }

    // @todo 3.0
    public function admin_stats_details()
    {
        $this->set(
            'registrations',
            $this->getMonthStats('User', 'registered')
        );
        $this->set('entries', $this->getMonthStats('Entry', 'time'));
    }

    protected function getAveragePPU($postingsPerYear, $activeUserPerYear)
    {
        if (empty($postingsPerYear) || empty($activeUserPerYear)) {
            return false;
        }
        $avgPostingsPerUser = [];
        foreach ($postingsPerYear['data'] as $key => $data) {
            list($year, $postings) = $data;
            $activeUsers = $activeUserPerYear['data'][$key][1];
            $avgPostingsPerUser[] = [$year, $postings / $activeUsers];
        }
        return $this->wrapData($avgPostingsPerUser);
    }

    protected function getCategoriesPerYear()
    {
        $results = $this->countYearStats(
            [
            'contain' => ['Category'],
            'fields' => ['COUNT(*) AS `count`', 'Category.category'],
            'group' => ['Category.category']
                    ],
            ['raw' => true]
        );
        if (empty($results)) {
            return false;
        }
        $data = [];
        foreach ($results as $dataset) {
            $category = $dataset['Category']['category'];
            $data[$category][] = [$dataset[0]['date'], $dataset[0]['count']];
        }
        $out = [];
        foreach ($data as $category => $dataset) {
            $out[] = $this->wrapData($dataset, ['label' => $category]);
        }
        return $out;
    }

    protected function wrapData(&$data, array $options = [])
    {
        return ['data' => $data] + $options;
    }

    protected function getUserWithPostingsPerYear()
    {
        return $this->countYearStats(['fields' => ['COUNT(DISTINCT `user_id`) AS `count`']]);
    }

    protected function getYearStats($model, $field)
    {
        return $this->countYearStats(
            ['fields' => ['COUNT(*) AS `count`']],
            [],
            $model,
            $field
        );
    }

    /**
     * count yearly stats
     *
     * @param $query
     * @param array $params
     * @param string $model
     * @param string $field
     * @return array|bool
     */
    protected function countYearStats($query, $params = [], $model = 'Entry', $field = 'time')
    {
        $params += [
        'raw' => false,
        'resolution' => 'year'
        ];

        $defaults = [
                    'contain' => false,
                    'fields' => ["YEAR({$field}) as `date`"],
                    'group' => ["YEAR({$field}) ASC"]
        ];
        if ($params['resolution'] === 'month') {
            $defaults['fields'] = "UNIX_TIMESTAMP(CONCAT(YEAR({$field}), '-', MONTH({$field}), '-01')) as `date`";
            $defaults['group'][] = "MONTH({$field}) ASC";
        }
        $query = array_merge_recursive($query, $defaults);
        if (empty($this->$model)) {
            $this->loadModel($model);
        }
        $results = $this->$model->find('all', $query);

        $periods = count($results);
        if (empty($results) || $periods < 2) {
            return false;
        }

        if ($params['raw']) {
            return $results;
        }

        $results = Hash::extract($results, '{n}.{n}');
        $data = [];
        foreach ($results as $d) {
            $data[] = [(string)$d['date'], $d['count']];
        }
        return $this->wrapData($data);
    }

    /**
     * @param $model
     * @param $field
     * @return array|bool
     */
    protected function getMonthStats($model, $field)
    {
        $results = $this->countYearStats(
            ['fields' => ['COUNT(*) AS `count`']],
            ['raw' => true, 'resolution' => 'month'],
            $model,
            $field
        );

        if (empty($results)) {
            return false;
        }

        $cumulated = [];
        $diff = [];
        $sum = 0;
        foreach ($results as $reg) {
            $sum += (int)$reg[0]['count'];
            $cumulated[] = array($reg[0]['date'] * 1000, $sum);
            $diff[] = array($reg[0]['date'] * 1000, (int)$reg[0]['count']);
        }
        return [
        $this->wrapData($diff, ['label' => __('New')]),
        $this->wrapData(
            $cumulated,
            ['yaxis' => 2, 'label' => __('Cumulated')]
        )
        ];
    }
}
