<?php

	namespace App\Controller\Component;

	use Cake\Controller\Component;
	use Cake\Event\Event;
	use Saito\JsData;

	class JsDataComponent extends Component {

		protected $_JsData;

		public function startup(Event $event) {
			$this->_JsData = JsData::getInstance();
		}

		public function __call($method, $params) {
			$proxy = array($this->_JsData, $method);
			if (is_callable($proxy)) {
				return call_user_func_array($proxy, $params);
			} else {
				return parent::__call($method, $params);
			}
		}

	}
