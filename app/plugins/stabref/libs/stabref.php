<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of stabref
 *
 * @author siezi
 */
class Stabref extends Object {
		public $lastAction 				= NULL;
		public $lastController 		= NULL;

		public $currentAction			= NULL;
		public $currentController	= NULL;

		public $referer = NULL;

		public function read(&$session) {
			$this->lastAction 		= $session->read('Stabref.lastAction');
			$this->lastController = $session->read('Stabref.lastController');

			$this->_updateDependent();
		}

		protected function _updateDependent() {
			$this->referer = '/';
			if (!empty($this->lastController)) {
				$this->referer .= $this->lastController;
			}
			if (!empty($this->lastAction)) {
				$this->referer .= "/{$this->lastAction}";
			}
		}

}
?>
