<?php

	App::uses('AppController', 'Controller');

	class AdminsController extends AppController {

		public $name = 'Admins';

		public $helpers = ['Admin', 'Flot', 'Sitemap.Sitemap'];

		public function admin_index() {
		}

		/**
		 * Show PHP-info
		 *
		 * @return void
		 */
		public function admin_phpinfo() {
		}

		public function admin_logs() {
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

		public function admin_plugins() {
		}

		public function admin_stats() {
			$postingsPA = $this->_getYearStats('Entry', 'time');
			$registrationsPA = $this->_getYearStats('User', 'registered');
			$activeUserPA = $this->_getUserWithPostingsPerYear();
			$averagePostingsPerUserPA = $this->_getAveragePPU($postingsPA,
					$activeUserPA);
			$this->set(compact('averagePostingsPerUserPA',
					'postingsPA', 'activeUserPA', 'registrationsPA'));
			$this->set('categoryPostingsPA', $this->_getCategoriesPerYear());
		}

		public function admin_stats_details() {
			$this->set('registrations',
					$this->_getMonthStats('User', 'registered'));
			$this->set('entries', $this->_getMonthStats('Entry', 'time'));
		}

		protected function _getAveragePPU($postingsPerYear, $activeUserPerYear) {
			if (empty($postingsPerYear) || empty($activeUserPerYear)) {
				return false;
			}
			$avgPostingsPerUser = [];
			foreach ($postingsPerYear['data'] as $key => $data) {
				list($year, $postings) = $data;
				$activeUsers = $activeUserPerYear['data'][$key][1];
				$avgPostingsPerUser[] = [$year, $postings / $activeUsers];
			}
			return $this->_wrapData($avgPostingsPerUser);
		}

		protected function _getCategoriesPerYear() {
			$results = $this->_countYearStats([
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
				$out[] = $this->_wrapData($dataset, ['label' => $category]);
			}
			return $out;
		}

		protected function _wrapData(&$data, array $options = []) {
			return ['data' => $data] + $options;
		}

		protected function _getUserWithPostingsPerYear() {
			return $this->_countYearStats(['fields' => ['COUNT(DISTINCT `user_id`) AS `count`']]);
		}

		protected function _getYearStats($model, $field) {
			return $this->_countYearStats(['fields' => ['COUNT(*) AS `count`']],
					[], $model, $field);
		}

		protected function _countYearStats($query, $params = [], $model = 'Entry', $field = 'time') {
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
			return $this->_wrapData($data);
		}

		protected function _getMonthStats($model, $field) {
			$results = $this->_countYearStats(
					['fields' => ['COUNT(*) AS `count`']],
					['raw' => true, 'resolution' => 'month'],
					$model,
					$field);

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
					$this->_wrapData($diff, ['label' => __('New')]),
					$this->_wrapData($cumulated,
							['yaxis' => 2, 'label' => __('Cumulated')])
			];
		}

	}
