<?php

App::import('Lib','stabref.stabref');

/**
 * Description of stabref
 *
 * @author siezi
 */

class StabrefComponent extends Stabref {

		public function initialize(&$controller, $settings = array()) {
			$this->read($controller->Session);
			if($controller->RequestHandler->isAjax() == false) {
				$this->_update($controller);
			}
			else {
				$this->_refresh($controller);
			}
		}

		public function shutdown(&$controller) {
			$this->_save($controller);
		}

		protected function _update(&$controller) {
			if (isset($controller->params['controller'])) {
				$this->currentController = $controller->params['controller'];
			}
			if (isset($controller->params['action'])) {
				$this->currentAction = $controller->params['action'];
			}
		}

		protected function _refresh(&$controller) {
			$this->currentAction = $this->lastAction;
			$this->currentController = $this->lastController;
			}

		protected function _save(&$controller) {
			$controller->Session->write('Stabref.lastAction', $this->currentAction);
			$controller->Session->write('Stabref.lastController', $this->currentController);
		}
}
?>
