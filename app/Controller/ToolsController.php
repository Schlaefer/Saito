<?php

	App::uses('AppController', 'Controller');

	/**
	 * Tools Controller
	 *
	 * @property Tool $Tool
	 */
	class ToolsController extends AppController {

		public $uses = NULL;

		/**
		 * Gives a deploy script a mean to empty PHP's APC-cache
		 *
		 * @link https://github.com/jadb/capcake/wiki/Capcake-and-PHP-APC>
		 */
		public function clearCache() {
			if ( in_array(@$_SERVER['REMOTE_ADDR'], array( '127.0.0.1', '::1' )) ) {
				apc_clear_cache();
				apc_clear_cache('user');
				apc_clear_cache('opcode');
				echo json_encode(array( 'APC Clear Cache' => true ));
			}
			exit;
		}

		public function beforeFilter() {
			$this->Auth->allow('clearCache');
		}

	}

