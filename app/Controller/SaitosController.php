<?php

	App::uses('AppController', 'Controller');

/**
 * Saitos Controller
 *
 */
	class SaitosController extends AppController {

		public $uses = array(
			'Shout'
		);

		public $components = [];

		public $autoRender = false;

/**
 * @return string
 * @throws BadRequestException
 */
		public function status() {
			$data = [
				'lastShoutId' => $this->Shout->findLastId()
			];
			$data = json_encode($data);
			if ($this->request->accepts('text/event-streams')) {
				return $this->_statusAsEventStream($data);
			} else {
				return $this->_statusAsJson($data);
			}
		}

		protected function _statusAsEventStream($data) {
			// time in ms to next request
			$_retry = '10000';
			$this->response->type(['eventstream' => 'text/event-stream']);
			$this->response->type('eventstream');
			$this->response->disableCache();
			$_out = '';
			$_out .= "retry: $_retry\n";
			$_out .= 'data: ' . $data . "\n\n";
			return $_out;
		}

		protected function _statusAsJson($data) {
			if ($this->request->is('ajax') === false) {
				throw new BadRequestException();
			}
			return $data;
		}

/**
 * Output current language strings as json
 */
		public function langJs() {
			// dummy translation to load po files
			__d('nondynamic', 'foo');
			__d('default', 'foo');
			$domains = I18n::domains();
			$translations = $domains['nondynamic'][Configure::read('Config.language')]['LC_MESSAGES'];
			$translations += $domains['default'][Configure::read('Config.language')]['LC_MESSAGES'];
			unset($translations['%po-header']);
			$this->response->type('json');
			$this->response->cache('-1 minute', '+1 hour');
			$this->response->compress();
			return json_encode($translations);
		}

		public function beforeFilter() {
			$this->Auth->allow(['status', 'langJs']);
		}

	}
