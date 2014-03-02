<?php

	App::uses('ModelBehavior', 'Model');
	App::uses('Cron', 'Cron.Lib');

	class CronBehavior extends ModelBehavior {

		protected $_Cron;

		public function setup(Model $model, $config = []) {
			$this->_Cron = Cron::getInstance();
			foreach ($config as $func => $options) {
				$this->addCronJob($model,
						$options['id'],
						$options['due'],
						[$model, $func]);
			}
		}

		public function clearHistoryCron() {
			$this->_Cron->clearHistory();
		}

		public function executeCron() {
			$this->_Cron->execute();
		}

		public function addCronJob(Model $Model) {
			call_user_func_array([$this->_Cron, 'addCronJob'], array_slice(func_get_args(), 1));
		}

	}
