<?php

	App::uses('AppController', 'Controller');

	/**
	 * Tools Controller
	 *
	 * @property Tool $Tool
	 */
	class ToolsController extends AppController {

		public $uses = array('Ecach');

		public $helpers = array(
			'JasmineJs.JasmineJs'
		);

		/**
		 * Emtpy out all caches
		 */
		public function admin_emptyCaches() {
			$this->Ecach->deleteAll(array('true = true'));

			Cache::clear(false);
			Cache::clear(false, 'perf-cheat');
			Cache::clearGroup('postings');
			Cache::clearGroup('persistent');
			Cache::clearGroup('models');
			$this->_clearApc();
			$this->Session->setFlash(__('Caches have been emptied.'), 'flash/notice');
			return $this->redirect($this->referer());
		}

		public function testJs() {
			if (Configure::read('debug') === 0) {
				echo 'Please activate debug mode.';
				exit;
			}

			$this->autoLayout = false;
		}

		/**
		 * Output current language strings as json
		 */
		public function langJs() {

			// dummy translation to load nondynamic.po
			__d('nondynamic', 'foo');
			$domains = I18n::domains();
			$translations =  $domains['nondynamic'][Configure::read('Config.language')]['LC_MESSAGES'];
			$translations +=  $domains['default'][Configure::read('Config.language')]['LC_MESSAGES'];
			unset($translations['%po-header']);
			// $this->response->type('javascript');
			$this->response->cache('-1 minute', '+1 hour');
			$this->response->compress();
			$this->set('lang', $translations);

		}

		/**
		 * Gives a deploy script a mean to empty PHP's APC-cache
		 *
		 * @link https://github.com/jadb/capcake/wiki/Capcake-and-PHP-APC>
		 */
		public function clearCache() {
			if (in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
				$this->_clearApc();
				echo json_encode(array('APC Clear Cache' => true));
			}
			exit;
		}

		/**
		 * Clears out the APC if available
		 */
		protected function _clearApc() {
			if (function_exists('apc_store')) {
				apc_clear_cache();
				apc_clear_cache('user');
				apc_clear_cache('opcode');
			}
		}

		public function beforeFilter() {
			parent::beforeFilter();
			$this->Auth->allow(
				'clearCache',
				'testJs',
				'langJs'
			);
		}

	}