<?php

	App::uses('AppController', 'Controller');

class AdminsController extends AppController {
	public $name 	= 'Admins';
	public $uses	= array();

	public function admin_index() {
	
	}

	public function admin_stats() {
		$this->set('user_registrations', $this->_getMonthStats('User', 'registered'));
		$this->set('entries', $this->_getMonthStats('Entry', 'time'));
	}

	protected function _getMonthStats($model, $field) {
		// read data from storage
		$this->loadModel($model);
		$storage_data = $this->$model->find(
				'all',
				array(
						'contain' => false,
						'fields' => array(
								"UNIX_TIMESTAMP(CONCAT(YEAR({$field}), '-', MONTH({$field}), '-01')) as `date`, COUNT(*) AS `count`"
						),
						'group' => array(
								"YEAR({$field}) ASC",
								"MONTH({$field}) ASC"
						)
				));

		// format data
		$cumulated = array();
		$diff = array();
		$sum = 0;
		foreach($storage_data as $reg) {
			$sum += (int)$reg[0]['count'];
			$cumulated[] = array($reg[0]['date'] * 1000, $sum);
			$diff[] = array($reg[0]['date'] * 1000, (int)$reg[0]['count']);
		}
		$data_json = json_encode(
				array(
						array('data' => $diff, 'label' => __('New')),
						array('data' => $cumulated,  'yaxis' => 2, 'label' => __('Cumulated')),
						));
		return $data_json;
	}

}
?>