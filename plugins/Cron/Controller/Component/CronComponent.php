<?php

	App::uses('Component', 'Controller');
	App::uses('Cron', 'Cron.Lib');

	class CronComponent extends Component {

		protected $_Cron = null;

		public function shutdown(Controller $controller) {
			$this->execute();
		}

		public function __call($method, $params) {
			if ($this->_Cron === null) {
				$this->_Cron = Cron::getInstance();
			}
			$proxy = [$this->_Cron, $method];
			if (is_callable($proxy)) {
				return call_user_func_array($proxy, $params);
			}
		}

	}

