<?php

	App::uses('AppController', 'Controller');

class AdminsController extends AppController {

	public $name = 'Admins';

	public $uses = [];

	public $helpers = [
		'Admin'
	];

	public function admin_index() {
	}

	public function admin_stats() {
		$this->set('user_registrations', $this->_getMonthStats('User', 'registered'));
		$this->set('entries', $this->_getMonthStats('Entry', 'time'));
	}

	public function admin_logs() {
		// order here is output order in frontend
		$_logsToRead = ['error', 'debug'];

		// will contain ['error' => '<string>', 'debug' => '<string>']
		$_logs = [];
		foreach ($_logsToRead as $_log) {
			$_path = LOGS . $_log . '.log';
			$_content = '';
			if (file_exists($_path)) {
				$_size = filesize($_path);
				$_content = file_get_contents($_path, false, null, $_size - 65536);
			}
			$_logs[$_log] = $_content;
		}

		$this->set('logs', $_logs);
	}

	protected function _getMonthStats($model, $field) {
		// read data from storage
		$this->loadModel($model);
		$storageData = $this->$model->find(
			'all',
			[
				'contain' => false,
				'fields' => [
					"UNIX_TIMESTAMP(CONCAT(YEAR({$field}), '-', MONTH({$field}), '-01')) as `date`, COUNT(*) AS `count`"
				],
				'group' => [
					"YEAR({$field}) ASC",
					"MONTH({$field}) ASC"
				]
			]
		);

		// format data
		$cumulated = [];
		$diff = [];
		$sum = 0;
		foreach ($storageData as $reg) {
			$sum += (int)$reg[0]['count'];
			$cumulated[] = array($reg[0]['date'] * 1000, $sum);
			$diff[] = array($reg[0]['date'] * 1000, (int)$reg[0]['count']);
		}
		$dataJson = json_encode(
			[
				['data' => $diff, 'label' => __('New')],
				['data' => $cumulated, 'yaxis' => 2, 'label' => __('Cumulated')]
			]
		);
		return $dataJson;
	}

}
